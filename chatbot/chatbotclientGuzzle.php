<?php
session_start();
require 'vendor/autoload.php';
require 'openai_functions.php';

use GuzzleHttp\Client;

// Ensure session history array exists early in the request lifecycle
if (!isset($_SESSION['content_history']) || !is_array($_SESSION['content_history'])) {
    $_SESSION['content_history'] = [];
}
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
                nextBtn.addEventListener('click', onClickFactory(function(){ nextClicked = true; askClicked = newChatClicked = false; }, nextBtn, 'Loading next…', 'Next…'));
            }
            if (newChatBtn) {
                newChatBtn.addEventListener('click', onClickFactory(function(){ newChatClicked = true; askClicked = nextClicked = false; }, newChatBtn, 'Starting new chat…', 'Starting…'));
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
    echo generateRadioOption('gpt-5', 'more accurate but slower using model gpt-5', $selected_model);
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