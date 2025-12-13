<?php
session_start();
require 'vendor/autoload.php';
require 'openai_functions.php';

use GuzzleHttp\Client;

// Ensure session history array exists early in the request lifecycle
if (!isset($_SESSION['content_history']) || !is_array($_SESSION['content_history'])) {
    $_SESSION['content_history'] = [];
}

// ---- Request size & upload error detection (before HTML output) ----
$flash_error = null;

// Helper: convert php.ini size values like "50M" to bytes
function _to_bytes($val){
    $val = trim((string)$val);
    $last = strtolower(substr($val, -1));
    $num = (int)$val;
    switch($last){
        case 'g': return $num * 1024 * 1024 * 1024;
        case 'm': return $num * 1024 * 1024;
        case 'k': return $num * 1024;
        default: return (int)$val;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Detect if post_max_size was exceeded: PHP leaves $_POST/$_FILES empty
    $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
    $postMax = _to_bytes(ini_get('post_max_size'));
    if ($contentLength > 0 && $postMax > 0 && $contentLength > $postMax && empty($_POST) && empty($_FILES)) {
        $flash_error = sprintf('The request is too large (%0.1f MB). Maximum allowed is %0.1f MB. Please choose a smaller image or increase server limits.', $contentLength / 1048576, $postMax / 1048576);
    }
    // If PHP accepted the POST, still check for per-file upload errors
    if (isset($_FILES['image_file']) && isset($_FILES['image_file']['error'])) {
        $err = (int)$_FILES['image_file']['error'];
        if ($err !== UPLOAD_ERR_OK && $err !== UPLOAD_ERR_NO_FILE) {
            switch ($err) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $uMax = _to_bytes(ini_get('upload_max_filesize'));
                    $flash_error = sprintf('The selected image exceeds the upload limit of %0.1f MB. Please choose a smaller image.', $uMax / 1048576);
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $flash_error = 'The image upload was incomplete. Please try again.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                case UPLOAD_ERR_CANT_WRITE:
                case UPLOAD_ERR_EXTENSION:
                    $flash_error = 'The server could not store the uploaded file. Please try again later or contact support.';
                    break;
                default:
                    $flash_error = 'An unexpected upload error occurred. Please try again.';
            }
        }
    }
}

$answeringOnLoad = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_button']));
?>

    <!DOCTYPE html>
    <html>

    <head>
        <?php include 'head.php'; ?>
        <style>
            .large-font {
                font-size: 34px;
            }
            .header-font {
                font-size: 24px;
            }
            /* Add CSS here */
            
            /* Ensure the file input (Choose File / No file chosen) matches the large label size */
            #image_file,
            input[type="file"] {
                font-size: 34px;
                line-height: 1.2em;
            }
            
            #scrollToTop {
                position: fixed;
                bottom: 20px;
                left: 40px;
                display: block; /* Always visible */
                background-color: #000;
                color: #fff;
                border: none;
                padding: 10px 20px;
                cursor: pointer;
                border-radius: 5px;
                z-index: 1000;
            }

            #scrollToTop:hover {
                background-color: #444;
            }
            /* Hourglass cursor when waiting */
            body.waiting, body.waiting * {
                cursor: wait !important;
            }

            /* Fullscreen loading overlay for mobile/desktop */
            #loadingOverlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.4);
                display: none; /* hidden by default */
                align-items: center;
                justify-content: center;
                z-index: 2000;
            }
            #loadingOverlay.show { display: flex; }
            #loadingOverlay .loader {
                width: 64px;
                height: 64px;
                border-radius: 50%;
                border: 8px solid #ffffff;
                border-top-color: transparent;
                animation: spin 1s linear infinite;
            }
            #loadingOverlay .label {
                color: #ffffff;
                font-size: 34px;
                margin-top: 16px;
                text-align: center;
            }
            @keyframes spin { to { transform: rotate(360deg); } }
        </style>
    </head>

    <body>
    <?php echo "<ul>"; ?>

    <?php if (!empty($flash_error)): ?>
        <div class="upload-error" role="alert" aria-live="assertive" style="
            margin: 1rem 2ch; padding: 12px 16px; border-radius: 6px;
            background: #ffeaea; color: #8a1f1f; border: 1px solid #f5c2c7; font-size: 20px;">
            <?php echo htmlspecialchars($flash_error); ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label class="ask large-font" for="model_choice">I want to chat:<br></label>

        <?php
        displayModelChoices();
        ?>
        <?php
           $display_text = getDisplayText();
         ?>
        <textarea name="input_text" class="input" rows="3" cols="40"><?php echo $display_text; ?></textarea>
        <br>
        <label class="ask large-font" for="image_file" style="margin-left: 2ch; ">Attach image (optional):</label>
        <br>
        <input type="file" name="image_file" id="image_file" accept="image/*" style="margin-left: 2ch;">
        <div id="imagePreviewWrapper" style="margin-left: 2ch; margin-top: 10px; display: none;">
            <img id="imagePreview" alt="Selected image preview" style="max-width: 90vw; max-height: 50vh; border: 1px solid #ccc; border-radius: 6px;">
            <div id="imagePreviewNote" style="font-size:16px; color:#555; margin-top:6px; display:none;"></div>
        </div>
        <script>
            (function(){
                var input = document.getElementById('image_file');
                var wrapper = document.getElementById('imagePreviewWrapper');
                var img = document.getElementById('imagePreview');
                var note = document.getElementById('imagePreviewNote');
                var currentObjectUrl = null;
                var STORAGE_KEY = 'chatbot_image_preview_dataurl';
                var TEXT_KEY = 'chatbot_textarea_value';
                var FILENAME_KEY = 'chatbot_image_preview_filename';
                // Rough per-origin sessionStorage limit is ~5MB; keep preview tiny
                var MAX_PREVIEW_DIM = 1280; // pixels max width/height for preview
                var PREVIEW_QUALITY = 0.7;  // JPEG/WebP quality

                function humanReadableSize(bytes){
                    try {
                        if (!isFinite(bytes) || bytes < 0) return '';
                        var units = ['B','KB','MB','GB','TB'];
                        var i = 0; var num = bytes;
                        while (num >= 1024 && i < units.length - 1) { num /= 1024; i++; }
                        return num.toFixed(num >= 10 || i === 0 ? 0 : 1) + ' ' + units[i];
                    } catch(e) { return ''; }
                }

                function setNote(text){
                    if (!note) return;
                    if (text) { note.textContent = text; note.style.display = 'block'; }
                    else { note.textContent = ''; note.style.display = 'none'; }
                }

                function clearPreview(){
                    if (currentObjectUrl) {
                        try { URL.revokeObjectURL(currentObjectUrl); } catch(e) {}
                        currentObjectUrl = null;
                    }
                    img.removeAttribute('src');
                    wrapper.style.display = 'none';
                    setNote('');
                }
                function showPreviewFromDataUrl(dataUrl){
                    if (!dataUrl) { clearPreview(); return; }
                    img.src = dataUrl;
                    wrapper.style.display = 'block';
                }
                function makeCanvasPreview(file, cb){
                    try {
                        var reader = new FileReader();
                        reader.onload = function(){
                            var imgEl = new Image();
                            imgEl.onload = function(){
                                var w = imgEl.naturalWidth || imgEl.width;
                                var h = imgEl.naturalHeight || imgEl.height;
                                var scale = Math.min(1, MAX_PREVIEW_DIM / Math.max(w, h));
                                var tw = Math.max(1, Math.round(w * scale));
                                var th = Math.max(1, Math.round(h * scale));
                                var canvas = document.createElement('canvas');
                                canvas.width = tw; canvas.height = th;
                                var ctx = canvas.getContext('2d');
                                ctx.drawImage(imgEl, 0, 0, tw, th);
                                var type = (/png$/i.test(file.type)) ? 'image/png' : (window.HTMLCanvasElement && canvas.toDataURL('image/webp', PREVIEW_QUALITY) ? 'image/webp' : 'image/jpeg');
                                var dataUrl;
                                try { dataUrl = canvas.toDataURL(type, PREVIEW_QUALITY); } catch(e) { dataUrl = canvas.toDataURL('image/jpeg', PREVIEW_QUALITY); }
                                cb(null, dataUrl);
                            };
                            imgEl.onerror = function(){ cb(new Error('preview-load-failed')); };
                            imgEl.src = reader.result;
                        };
                        reader.onerror = function(){ cb(new Error('file-read-failed')); };
                        reader.readAsDataURL(file);
                    } catch(e) { cb(e); }
                }
                function showPreview(file){
                    if (!file || !file.type || !/^image\//i.test(file.type)) {
                        clearPreview();
                        try { sessionStorage.removeItem(STORAGE_KEY); } catch(e) {}
                        try { sessionStorage.removeItem(FILENAME_KEY); } catch(e) {}
                        return;
                    }
                    // Immediate preview using a blob URL
                    clearPreview();
                    currentObjectUrl = URL.createObjectURL(file);
                    img.onload = function(){
                        try { URL.revokeObjectURL(currentObjectUrl); } catch(e) {}
                        currentObjectUrl = null;
                    };
                    img.src = currentObjectUrl;
                    wrapper.style.display = 'block';

                    // Create a tiny, compressed preview for sessionStorage restore after reload
                    makeCanvasPreview(file, function(err, dataUrl){
                        if (err) { setNote(file && file.name ? file.name : 'Preview may not persist after submit.'); return; }
                        try {
                            // Persist tiny preview and filename for restore after submit
                            sessionStorage.setItem(STORAGE_KEY, dataUrl);
                            try { sessionStorage.setItem(FILENAME_KEY, file.name || ''); } catch(e2) {}
                            setNote(file && file.name ? file.name : '');
                        } catch(e) {
                            // Storage likely exceeded (~5MB); warn user but keep working
                            setNote((file && file.name ? file.name + ' — ' : '') + 'Preview cannot persist after submit due to browser limits. The image will still be uploaded.');
                        }
                    });
                }

                // Restore persisted preview and textarea on load (after a submit reload)
                try {
                    var saved = sessionStorage.getItem(STORAGE_KEY);
                    if (saved) {
                        showPreviewFromDataUrl(saved);
                        var fname = '';
                        try { fname = sessionStorage.getItem(FILENAME_KEY) || ''; } catch(e2) {}
                        if (fname) setNote(fname);
                        else setNote('Restored preview.');
                    }
                } catch(e) {}

                // Save/restore textarea value as an extra safeguard
                try {
                    var textarea = document.querySelector('textarea[name="input_text"]');
                    if (textarea) {
                        // On load, prefill if server didn't
                        if (!textarea.value) {
                            var savedText = sessionStorage.getItem(TEXT_KEY);
                            if (savedText) textarea.value = savedText;
                        }
                        textarea.addEventListener('input', function(){
                            try { sessionStorage.setItem(TEXT_KEY, textarea.value); } catch(e) {}
                        });
                    }
                } catch(e) {}

                if (input) {
                    input.addEventListener('change', function(){
                        var file = this.files && this.files[0];
                        if (file) {
                            // Soft client-side warning if image is very large
                            if (file.size > 19 * 1024 * 1024) {
                                alert('Selected image is larger than 19 MB. Your server may reject it. Consider choosing a smaller image.');
                            }
                            showPreview(file);
                        } else {
                            clearPreview();
                            try { sessionStorage.removeItem(STORAGE_KEY); } catch(e) {}
                        }
                    });
                }
            })();
        </script>
        <br><br><br>
        <input type="submit" name="submit_button" class="ask green-background" value="ASK">
        <input type="submit" name="clean_button" class="ask red-background" value="NEXT">
        <input type="submit" name="clear_history_button" class="ask red-background" value="NEW CHAT">
    </form>
    </p>

    <!-- Cross-platform loading overlay (shown during actions) -->
    <div id="loadingOverlay" aria-live="polite" role="status" aria-busy="true">
        <div style="display:flex; flex-direction:column; align-items:center;">
            <div class="loader" aria-hidden="true"></div>
            <div class="label">Working…</div>
        </div>
    </div>

    <span style="font-size: 40px;">Copy to clipboard</span>
    <br>
    <button class="copy red-background" onclick="copyOutputToClipboard(event)"
            style="font-size:40px; padding:10px;">Complete chat
    </button>
    <button class="copy red-background" onclick="copyAnswerToClipboard(event)"
            style="font-size:40px; padding:10px;">Last answer
    </button>
    <br>
    <!-- Text-to-Speech controls -->
    <span style="font-size: 40px;">Audio</span>
    <br>
    <button id="playAudioBtn" class="copy red-background" onclick="playAnswerAudio(event)"
            style="font-size:40px; padding:10px;">Play Audio</button>
    <button id="stopAudioBtn" class="copy red-background" onclick="stopAnswerAudio(event)" disabled
            style="font-size:40px; padding:10px;">Stop</button>
    <?php

    // Ensure content history is initialized before any processing
    initializeContentHistory();

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handleFormSubmission();
    }

    ?>
    <?php

    // Output content history as text
    $content_history_text = implode("\n", $_SESSION['content_history']);
    ?>
    <textarea id="outputhistory" style="display:none;"><?php echo htmlentities($content_history_text); ?></textarea>

    <script>
        function copyOutputToClipboard(event) {
            event.preventDefault();
            const outputText = document.getElementById("outputhistory").value;
            navigator.clipboard.writeText(outputText).then(() => {
                alert("Copied to clipboard: " + outputText);
            }).catch(err => {
                console.error('Could not copy text: ', err);
            });
        }

        function copyAnswerToClipboard(event) {
            event.preventDefault();
            const outputText = document.getElementById("outputhistory").value;
            var lastIndex = outputText.lastIndexOf("ANSWER: ");
            if (lastIndex !== -1) {
                var startIndex = lastIndex + "ANSWER: ".length;
                var endText = outputText.substring(startIndex);
                const tempTextarea = document.createElement("textarea");
                tempTextarea.value = endText;
                document.body.appendChild(tempTextarea);
                tempTextarea.select();
                tempTextarea.setSelectionRange(0, 99999);
                document.execCommand("copy");
                document.body.removeChild(tempTextarea);
                alert("Copied to clipboard: " + endText);
            }
        }

        // ===== Text-to-Speech (TTS) for last ANSWER =====
        (function(){
            var currentUtterance = null;
            var isSpeaking = false;

            function getLastAnswerText() {
                var outputText = document.getElementById('outputhistory').value || '';
                var lastIndex = outputText.lastIndexOf('ANSWER: ');
                if (lastIndex === -1) return '';
                return outputText.substring(lastIndex + 'ANSWER: '.length).trim();
            }

            function setPlaying(playing) {
                isSpeaking = !!playing;
                var playBtn = document.getElementById('playAudioBtn');
                var stopBtn = document.getElementById('stopAudioBtn');
                if (playBtn) playBtn.disabled = playing; // disable Play while speaking
                if (stopBtn) stopBtn.disabled = !playing; // enable Stop while speaking
            }

            window.playAnswerAudio = function(event){
                if (event && event.preventDefault) event.preventDefault();
                if (!('speechSynthesis' in window)) {
                    alert('Text-to-Speech is not supported in this browser.');
                    return;
                }
                // If already speaking, ignore or restart
                if (window.speechSynthesis.speaking) {
                    try { window.speechSynthesis.cancel(); } catch(e) {}
                }

                var text = getLastAnswerText();
                if (!text) {
                    alert('No answer to play yet.');
                    return;
                }
                currentUtterance = new SpeechSynthesisUtterance(text);
                // Optional voice selection: prefer an English voice if available
                try {
                    var voices = window.speechSynthesis.getVoices();
                    if (voices && voices.length) {
                        var preferred = voices.find(v => /en(-|_|\b)/i.test(v.lang)) || voices[0];
                        currentUtterance.voice = preferred;
                    }
                } catch (e) {}
                currentUtterance.rate = 1.0; // 0.1 — 10
                currentUtterance.pitch = 1.0; // 0 — 2
                currentUtterance.onstart = function(){ setPlaying(true); };
                currentUtterance.onend = function(){ setPlaying(false); currentUtterance = null; };
                currentUtterance.onerror = function(){ setPlaying(false); currentUtterance = null; };

                try {
                    window.speechSynthesis.speak(currentUtterance);
                } catch (e) {
                    console.error('TTS speak failed:', e);
                    alert('Could not start speech.');
                    setPlaying(false);
                }
            };

            window.stopAnswerAudio = function(event){
                if (event && event.preventDefault) event.preventDefault();
                try {
                    if ('speechSynthesis' in window) {
                        window.speechSynthesis.cancel();
                    }
                } catch (e) {}
                setPlaying(false);
                currentUtterance = null;
            };

            // In case page restores with speech ongoing, ensure button states
            document.addEventListener('visibilitychange', function(){
                if (!document.hidden) {
                    setPlaying(window.speechSynthesis && window.speechSynthesis.speaking);
                }
            });
            window.addEventListener('load', function(){ setPlaying(false); });
        })();
    </script>
    <!-- Add the button -->
    <button id="scrollToTop" onclick="scrollToTop()" style="font-size: 40px;">Top</button>

    <!-- Add JS here -->
    <script>
        // Scroll to the top of the page when the button is clicked
        function scrollToTop() {
            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
        }

        // Show cross-platform loading overlay for ASK, NEXT, and NEW CHAT
        window.addEventListener('DOMContentLoaded', function () {
            var askBtn = document.querySelector('input[name="submit_button"]');
            var nextBtn = document.querySelector('input[name="clean_button"]');
            var newChatBtn = document.querySelector('input[name="clear_history_button"]');
            var form = document.querySelector('form');
            var overlay = document.getElementById('loadingOverlay');
            var overlayLabel = overlay ? overlay.querySelector('.label') : null;

            // Legacy fallback flags for browsers without e.submitter
            var askClicked = false, nextClicked = false, newChatClicked = false;

            // Track the button we disabled to restore on failsafe
            var activeButton = null;
            var failsafeTimer = null;

            function setOverlayLabel(text) {
                if (overlayLabel) overlayLabel.textContent = text;
            }

            function showLoadingFor(button, overlayText, buttonText) {
                document.body.classList.add('waiting'); // desktop cursor
                document.body.setAttribute('aria-busy', 'true');
                if (overlay) overlay.classList.add('show');

                if (button) {
                    activeButton = button;
                    // store original value only once
                    if (!button.dataset.originalValue) {
                        button.dataset.originalValue = button.value;
                    }
                    // Do not disable the button before submission to ensure its name/value is posted
                    // button.disabled = true;
                    // button.setAttribute('aria-disabled', 'true');
                    if (buttonText) button.value = buttonText;
                }
                setOverlayLabel(overlayText || 'Working…');

                if (!failsafeTimer) {
                    failsafeTimer = setTimeout(hideLoading, 30000); // auto-revert if no navigation
                }
            }

            function hideLoading() {
                document.body.classList.remove('waiting');
                document.body.removeAttribute('aria-busy');
                if (overlay) overlay.classList.remove('show');
                if (activeButton) {
                    activeButton.disabled = false;
                    activeButton.removeAttribute('aria-disabled');
                    if (activeButton.dataset.originalValue) {
                        activeButton.value = activeButton.dataset.originalValue;
                        delete activeButton.dataset.originalValue;
                    }
                    activeButton = null;
                }
                if (failsafeTimer) {
                    clearTimeout(failsafeTimer);
                    failsafeTimer = null;
                }
                askClicked = nextClicked = newChatClicked = false;
            }

            function onClickFactory(flagSetter, button, overlayText, buttonText) {
                return function () {
                    flagSetter();
                    showLoadingFor(button, overlayText, buttonText);
                };
            }

            if (askBtn) {
                askBtn.addEventListener('click', onClickFactory(function(){ askClicked = true; nextClicked = newChatClicked = false; }, askBtn, 'Asking…', 'Asking…'));
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', function(){
                    nextClicked = true; askClicked = newChatClicked = false;
                    // Ensure the question field initializes clean for NEXT
                    try { sessionStorage.removeItem('chatbot_textarea_value'); } catch(e) {}
                    try {
                        var ta = document.querySelector('textarea[name="input_text"]');
                        if (ta) { ta.value = ''; }
                    } catch(e) {}
                    onClickFactory(function(){}, nextBtn, 'Loading next…', 'Next…')();
                });
            }
            if (newChatBtn) {
                newChatBtn.addEventListener('click', function(){
                    newChatClicked = true; askClicked = nextClicked = false;
                    // Clear persisted image preview and textarea when starting a new chat
                    try { sessionStorage.removeItem('chatbot_image_preview_dataurl'); } catch(e) {}
                    try { sessionStorage.removeItem('chatbot_textarea_value'); } catch(e) {}
                    try {
                        var ta2 = document.querySelector('textarea[name="input_text"]');
                        if (ta2) { ta2.value = ''; }
                    } catch(e) {}
                    onClickFactory(function(){}, newChatBtn, 'Starting new chat…', 'Starting…')();
                });
            }

            if (form) {
                form.addEventListener('submit', function (e) {
                    var submitter = e.submitter || null; // modern browsers
                    var name = submitter ? submitter.name : null;

                    if (!name) {
                        // Fallback to flags when e.submitter is unavailable
                        if (askClicked) name = 'submit_button';
                        else if (nextClicked) name = 'clean_button';
                        else if (newChatClicked) name = 'clear_history_button';
                    }

                    // For NEXT and NEW CHAT, ensure the question field does not get restored
                    if (name === 'clean_button' || name === 'clear_history_button') {
                        try { sessionStorage.removeItem('chatbot_textarea_value'); } catch(e) {}
                    }

                    if (name === 'submit_button') {
                        showLoadingFor(askBtn, 'Asking…', 'Asking…');
                    } else if (name === 'clean_button') {
                        showLoadingFor(nextBtn, 'Loading next…', 'Next…');
                    } else if (name === 'clear_history_button') {
                        showLoadingFor(newChatBtn, 'Starting new chat…', 'Starting…');
                    } else {
                        // Not one of our buttons; ensure no stale state
                        hideLoading();
                    }
                });
            }
        });
    </script>
<?php if (!empty($answeringOnLoad)): ?>
    <script>
        // Show brief "answering" overlay as soon as the DOM is ready (or immediately if it already is)
        (function(){
            function showAnswering(){
                try {
                    var overlay = document.getElementById('loadingOverlay');
                    var attempts = 0;
                    function doShow(ov){
                        document.body.classList.add('waiting');
                        document.body.setAttribute('aria-busy', 'true');
                        var overlayLabel = ov ? ov.querySelector('.label') : null;
                        if (overlayLabel) overlayLabel.textContent = 'answering';
                        if (ov) ov.classList.add('show');
                        // Let the browser paint the new content, then hide overlay shortly after
                        requestAnimationFrame(function(){
                            setTimeout(function(){
                                document.body.classList.remove('waiting');
                                document.body.removeAttribute('aria-busy');
                                if (ov) ov.classList.remove('show');
                            }, 600);
                        });
                    }
                    if (!overlay) {
                        // If the overlay isn't in the DOM yet, retry briefly
                        (function retry(){
                            attempts++;
                            var ov = document.getElementById('loadingOverlay');
                            if (ov || attempts > 10) {
                                doShow(ov);
                                return;
                            }
                            requestAnimationFrame(retry);
                        })();
                        return;
                    }
                    doShow(overlay);
                } catch(e) {}
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showAnswering);
            } else {
                // DOM is already parsed; run immediately
                showAnswering();
            }
        })();
    </script>
<?php endif; ?>
    </body>

    </html>

<?php

function handleFormSubmission(): void
{
    if (isset($_POST['model_choice_chosen'])) {
        $_SESSION['model_choice'] = $_POST['model_choice_chosen'];
    }

    if (isset($_POST['submit_button']) && (!empty($_POST['input_text']) || (isset($_FILES['image_file']) && isset($_FILES['image_file']['error']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK))) {
        processUserInput($_POST['input_text'] ?? '');
    }

    if (isset($_POST['clean_button'])) {
        cleanInputText();
    }

    if (isset($_POST['clear_history_button'])) {
        clearContentHistory();
    }
}

function displayModelChoices(): void
{
    // Check POST first, then fallback to SESSION, then default value
    $selected_model = $_POST['model_choice_chosen'] ?? $_SESSION['model_choice'] ?? 'gpt-5-mini';
    // Store the selected model in session
    $_SESSION['model_choice'] = $selected_model;
    
    // echo "<br>" . $selected_model . "<br>";
    echo generateRadioOption('gpt-5-mini', 'simple and fast using model gpt-5-mini', $selected_model);
    echo generateRadioOption('gpt-5.2', 'more accurate but slower using model gpt-5.2', $selected_model);
}

function generateRadioOption($id, $label, $selected_model): string
{
    $checked = $selected_model === $id ? 'checked' : '';
    return <<<HTML
    <input type="radio" id="$id" name="model_choice_chosen" value="$id" $checked class="large-font">
    <label for="$id" class="large-font">$label</label><br>
HTML;
}

function initializeContentHistory(): void
{
    if (!isset($_SESSION['content_history'])) {
        $_SESSION['content_history'] = [];
    }
}

function getDisplayText(): string
{
    if (isset($_POST['submit_button'])) {
        return htmlentities($_POST['input_text']);
    }
    return '';
}

function processUserInput($input_text): void
{
    $openai_data = init_openai();
    $client = $openai_data[1];
    $selected_model = $_SESSION['model_choice'] ?: 'gpt-5-mini';
    $content_history = &$_SESSION['content_history'];

    // Handle optional image upload
    $image_data_url = null;
    if (isset($_FILES['image_file']) && isset($_FILES['image_file']['error']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['image_file']['tmp_name'];
        $mime = $_FILES['image_file']['type'] ?: null;
        if (!$mime && function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $tmpName);
                finfo_close($finfo);
            }
        }
        // Accept only common image MIME types
        $allowed = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
        if ($mime && in_array($mime, $allowed, true)) {
            $bytes = @file_get_contents($tmpName);
            if ($bytes !== false) {
                $base64 = base64_encode($bytes);
                $image_data_url = "data:$mime;base64,$base64";
            }
        }
    }

    $myquestion = "QUESTION: " . $input_text . ($image_data_url ? " [image attached]" : "");
    // in openai_functions all output is generated
    $mycompletion = "ANSWER: " . get_openai_response_for_model($input_text, $selected_model, $client, $content_history, $image_data_url);
    $content_history[] = $myquestion;
    $content_history[] = $mycompletion;

    $_SESSION['content_history'] = $content_history;
}

function cleanInputText(): void
{
    $_POST['input_text'] = '';
}

function clearContentHistory(): void
{
    $_SESSION['content_history'] = [];
}

?>