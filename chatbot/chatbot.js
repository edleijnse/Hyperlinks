window.showAnswering = function() {
    try {
        var overlay = document.getElementById('loadingOverlay');
        var attempts = 0;
        function doShow(ov){
            document.body.classList.add('waiting');
            document.body.setAttribute('aria-busy', 'true');
            var overlayLabel = ov ? ov.querySelector('.label') : null;
            if (overlayLabel) overlayLabel.textContent = 'answering';
            if (ov) ov.classList.add('show');
            requestAnimationFrame(function(){
                setTimeout(function(){
                    document.body.classList.remove('waiting');
                    document.body.removeAttribute('aria-busy');
                    if (ov) ov.classList.remove('show');
                }, 600);
            });
        }
        if (!overlay) {
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
};

(function(){
    var input = document.getElementById('image_file');
    var wrapper = document.getElementById('imagePreviewWrapper');
    var img = document.getElementById('imagePreview');
    var note = document.getElementById('imagePreviewNote');
    var currentObjectUrl = null;
    var STORAGE_KEY = 'chatbot_image_preview_dataurl';
    var TEXT_KEY = 'chatbot_textarea_value';
    var FILENAME_KEY = 'chatbot_image_preview_filename';
    var MAX_PREVIEW_DIM = 1280;
    var PREVIEW_QUALITY = 0.7;

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
        clearPreview();
        currentObjectUrl = URL.createObjectURL(file);
        img.onload = function(){
            try { URL.revokeObjectURL(currentObjectUrl); } catch(e) {}
            currentObjectUrl = null;
        };
        img.src = currentObjectUrl;
        wrapper.style.display = 'block';

        makeCanvasPreview(file, function(err, dataUrl){
            if (err) { setNote(file && file.name ? file.name : 'Preview may not persist after submit.'); return; }
            try {
                sessionStorage.setItem(STORAGE_KEY, dataUrl);
                try { sessionStorage.setItem(FILENAME_KEY, file.name || ''); } catch(e2) {}
                setNote(file && file.name ? file.name : '');
            } catch(e) {
                setNote((file && file.name ? file.name + ' — ' : '') + 'Preview cannot persist after submit due to browser limits. The image will still be uploaded.');
            }
        });
    }

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

    if (input) {
        input.addEventListener('change', function(){
            var file = input.files[0];
            showPreview(file);
        });
    }

    var ta = document.querySelector('textarea[name="input_text"]');
    if (ta) {
        try {
            var restored = sessionStorage.getItem(TEXT_KEY);
            if (restored && !ta.value.trim()) { ta.value = restored; }
        } catch(e) {}
        ta.addEventListener('input', function(){
            try { sessionStorage.setItem(TEXT_KEY, ta.value); } catch(e) {}
        });
    }
})();

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
        var playBtnDe = document.getElementById('playAudioBtnDe');
        var playBtnEs = document.getElementById('playAudioBtnEs');
        var playBtnFr = document.getElementById('playAudioBtnFr');
        var playBtnIt = document.getElementById('playAudioBtnIt');
        var playBtnNl = document.getElementById('playAudioBtnNl');
        var playBtnEl = document.getElementById('playAudioBtnEl');
        var stopBtn = document.getElementById('stopAudioBtn');
        
        const playBtns = [playBtn, playBtnDe, playBtnEs, playBtnFr, playBtnIt, playBtnNl, playBtnEl];
        playBtns.forEach(btn => {
            if (btn) {
                btn.disabled = playing;
                btn.style.opacity = playing ? '0.5' : '1';
            }
        });
        if (stopBtn) stopBtn.disabled = !playing;
    }

    window.playAnswerAudio = function(event, lang){
        if (event && event.preventDefault) event.preventDefault();
        if (!('speechSynthesis' in window)) {
            alert('Text-to-Speech is not supported in this browser.');
            return;
        }
        if (window.speechSynthesis.speaking) {
            try { window.speechSynthesis.cancel(); } catch(e) {}
        }

        var text = getLastAnswerText();
        if (!text) {
            alert('No answer to play yet.');
            return;
        }
        currentUtterance = new SpeechSynthesisUtterance(text);
        
        // Set the requested language immediately
        var requestedLang = lang || 'en-US';
        currentUtterance.lang = requestedLang;

        try {
            var voices = window.speechSynthesis.getVoices();
            if (voices && voices.length) {
                var preferred = voices.find(v => v.lang.toLowerCase() === requestedLang.toLowerCase()) || 
                                voices.find(v => v.lang.toLowerCase().startsWith(requestedLang.toLowerCase().split('-')[0]));
                
                if (preferred) {
                    currentUtterance.voice = preferred;
                    currentUtterance.lang = preferred.lang;
                } else {
                    // Fallback to English only if we couldn't find ANY match for requested language
                    var englishFallback = voices.find(v => /en(-|_|\b)/i.test(v.lang));
                    if (englishFallback && requestedLang.toLowerCase().startsWith('en')) {
                        currentUtterance.voice = englishFallback;
                        currentUtterance.lang = englishFallback.lang;
                    }
                }
            }
        } catch (e) {}
        currentUtterance.rate = 1.0;
        currentUtterance.pitch = 1.0;
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

    document.addEventListener('visibilitychange', function(){
        if (!document.hidden) {
            setPlaying(window.speechSynthesis && window.speechSynthesis.speaking);
        }
    });
    window.addEventListener('load', function(){ setPlaying(false); });
})();

(function(){
    var voiceBtnEn = document.getElementById('voiceInputBtn');
    var voiceBtnDe = document.getElementById('voiceInputBtnDe');
    var voiceBtnEs = document.getElementById('voiceInputBtnEs');
    var voiceBtnFr = document.getElementById('voiceInputBtnFr');
    var voiceBtnIt = document.getElementById('voiceInputBtnIt');
    var voiceBtnNl = document.getElementById('voiceInputBtnNl');
    var voiceBtnEl = document.getElementById('voiceInputBtnEl');
    var voiceProcessBtn = document.getElementById('voiceProcessBtn');
    var voiceStatus = document.getElementById('voiceStatus');
    var textArea = document.getElementById('input_text');
    var recognition = null;
    var isRecording = false;
    var currentActiveBtn = null;

    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        [voiceBtnEn, voiceBtnDe, voiceBtnEs, voiceBtnFr, voiceBtnIt, voiceBtnNl, voiceBtnEl, voiceProcessBtn].forEach(btn => {
            if (btn) btn.style.display = 'none';
        });
        return;
    }

    var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;

    recognition.onstart = function() {
        isRecording = true;
        if (currentActiveBtn) {
            currentActiveBtn.classList.add('recording');
        }
        if (voiceStatus) {
            voiceStatus.classList.remove('hidden');
        }
    };

    recognition.onresult = function(event) {
        var transcript = event.results[0][0].transcript;
        if (textArea) {
            var currentVal = textArea.value.trim();
            textArea.value = (currentVal ? currentVal + ' ' : '') + transcript;
            textArea.dispatchEvent(new Event('input'));
        }
        if (voiceProcessBtn) {
            voiceProcessBtn.classList.remove('hidden');
        }
    };

    recognition.onerror = function(event) {
        console.error('Speech recognition error', event.error);
        if (event.error === 'not-allowed') {
            alert('Microphone access denied. Please allow microphone access in your browser settings.');
        } else if (event.error === 'no-speech') {
            alert('No speech detected. Please try again.');
        } else {
            alert('Speech recognition error: ' + event.error);
        }
        stopRecording();
    };

    recognition.onend = function() {
        if (isRecording) {
            stopRecording();
        }
        if (voiceStatus) {
            voiceStatus.classList.add('hidden');
        }
    };

    function stopRecording() {
        isRecording = false;
        if (currentActiveBtn) {
            currentActiveBtn.classList.remove('recording');
        }
        currentActiveBtn = null;
        try { recognition.stop(); } catch(e) {}
    }

    function startRecording(btn, lang) {
        if (isRecording) {
            stopRecording();
            return;
        }

        if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
            alert('Speech recognition often requires a secure connection (HTTPS). Please check your connection.');
        }

        currentActiveBtn = btn;
        recognition.lang = lang;

        try {
            recognition.start();
        } catch(e) {
            console.error('Speech recognition start failed', e);
            alert('Could not start speech recognition: ' + e.message);
            currentActiveBtn = null;
        }
    }

    if (voiceBtnEn) {
        voiceBtnEn.addEventListener('click', function(e) {
            e.preventDefault();
            startRecording(voiceBtnEn, 'en-US');
        });
    }
    if (voiceBtnDe) {
        voiceBtnDe.addEventListener('click', function(e) {
            e.preventDefault();
            startRecording(voiceBtnDe, 'de-DE');
        });
    }
    if (voiceBtnEs) {
        voiceBtnEs.addEventListener('click', function(e) {
            e.preventDefault();
            startRecording(voiceBtnEs, 'es-ES');
        });
    }
    if (voiceBtnFr) {
        voiceBtnFr.addEventListener('click', function(e) {
            e.preventDefault();
            startRecording(voiceBtnFr, 'fr-FR');
        });
    }
    if (voiceBtnIt) {
        voiceBtnIt.addEventListener('click', function(e) {
            e.preventDefault();
            startRecording(voiceBtnIt, 'it-IT');
        });
    }
    if (voiceBtnNl) {
        voiceBtnNl.addEventListener('click', function(e) {
            e.preventDefault();
            startRecording(voiceBtnNl, 'nl-NL');
        });
    }
    if (voiceBtnEl) {
        voiceBtnEl.addEventListener('click', function(e) {
            e.preventDefault();
            startRecording(voiceBtnEl, 'el-GR');
        });
    }

    if (voiceProcessBtn) {
        voiceProcessBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var askBtn = document.querySelector('input[name="submit_button"]');
            if (askBtn) {
                askBtn.click();
            }
        });
    }
})();

function scrollToTop() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
}

window.addEventListener('DOMContentLoaded', function () {
    var askBtn = document.querySelector('input[name="submit_button"]');
    var newChatBtn = document.querySelector('input[name="clear_history_button"]');
    var form = document.querySelector('form');
    var overlay = document.getElementById('loadingOverlay');
    var overlayLabel = overlay ? overlay.querySelector('.label') : null;

    var askClicked = false, newChatClicked = false;
    var activeButton = null;
    var failsafeTimer = null;

    function setOverlayLabel(text) {
        if (overlayLabel) overlayLabel.textContent = text;
    }

    function showLoadingFor(button, overlayText, buttonText) {
        document.body.classList.add('waiting');
        document.body.setAttribute('aria-busy', 'true');
        if (overlay) overlay.classList.add('show');

        if (button) {
            activeButton = button;
            if (!button.dataset.originalValue) {
                button.dataset.originalValue = button.value;
            }
            if (buttonText) button.value = buttonText;
        }
        setOverlayLabel(overlayText || 'Working…');

        if (!failsafeTimer) {
            failsafeTimer = setTimeout(hideLoading, 30000);
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
        askClicked = newChatClicked = false;
    }

    function onClickFactory(flagSetter, button, overlayText, buttonText) {
        return function () {
            flagSetter();
            showLoadingFor(button, overlayText, buttonText);
        };
    }

    if (askBtn) {
        askBtn.addEventListener('click', function(){
            askClicked = true; newChatClicked = false;
            try { sessionStorage.removeItem('chatbot_textarea_value'); } catch(e) {}
            onClickFactory(function(){}, askBtn, 'Asking…', 'Asking…')();
        });
    }
    if (newChatBtn) {
        newChatBtn.addEventListener('click', function(){
            newChatClicked = true; askClicked = false;
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
            var submitter = e.submitter || null;
            var name = submitter ? submitter.name : null;

            if (!name) {
                if (askClicked) name = 'submit_button';
                else if (newChatClicked) name = 'clear_history_button';
            }

            if (name === 'clear_history_button') {
                try { sessionStorage.removeItem('chatbot_textarea_value'); } catch(e) {}
            }

            if (name === 'submit_button') {
                showLoadingFor(askBtn, 'Asking…', 'Asking…');
            } else if (name === 'clear_history_button') {
                showLoadingFor(newChatBtn, 'Starting new chat…', 'Starting…');
            } else {
                hideLoading();
            }
        });
    }

    // Warm up speech synthesis voices
    if ('speechSynthesis' in window) {
        window.speechSynthesis.getVoices();
        if (window.speechSynthesis.onvoiceschanged !== undefined) {
            window.speechSynthesis.onvoiceschanged = function() {
                window.speechSynthesis.getVoices();
            };
        }
    }
});
