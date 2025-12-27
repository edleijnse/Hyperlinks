<?php
session_start();
require 'vendor/autoload.php';
require 'openai_functions.php';

use GuzzleHttp\Client;

/**
 * Initialize session history if not already set.
 */
function initSession(): void {
    if (!isset($_SESSION['content_history']) || !is_array($_SESSION['content_history'])) {
        $_SESSION['content_history'] = [];
    }
}

/**
 * Convert php.ini size values (e.g., "50M") to bytes.
 */
function toBytes($val): int {
    $val = trim((string)$val);
    $last = strtolower(substr($val, -1));
    $num = (int)$val;
    switch ($last) {
        case 'g': return $num * 1024 * 1024 * 1024;
        case 'm': return $num * 1024 * 1024;
        case 'k': return $num * 1024;
        default: return $num;
    }
}

/**
 * Detect upload errors and request size issues.
 */
function detectUploadErrors(): ?string {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return null;
    }

    $contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
    $postMax = toBytes(ini_get('post_max_size'));
    if ($contentLength > 0 && $postMax > 0 && $contentLength > $postMax && empty($_POST) && empty($_FILES)) {
        return sprintf('The request is too large (%0.1f MB). Maximum allowed is %0.1f MB.', $contentLength / 1048576, $postMax / 1048576);
    }

    if (isset($_FILES['image_file']['error'])) {
        $err = (int)$_FILES['image_file']['error'];
        if ($err !== UPLOAD_ERR_OK && $err !== UPLOAD_ERR_NO_FILE) {
            switch ($err) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $uMax = toBytes(ini_get('upload_max_filesize'));
                    return sprintf('The selected image exceeds the upload limit of %0.1f MB.', $uMax / 1048576);
                case UPLOAD_ERR_PARTIAL:
                    return 'The image upload was incomplete.';
                case UPLOAD_ERR_NO_TMP_DIR:
                case UPLOAD_ERR_CANT_WRITE:
                case UPLOAD_ERR_EXTENSION:
                    return 'The server could not store the uploaded file.';
                default:
                    return 'An unexpected upload error occurred.';
            }
        }
    }
    return null;
}

/**
 * Process image upload and return data URL.
 */
function handleImageUpload(): ?string {
    if (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $tmpName = $_FILES['image_file']['tmp_name'];
    $mime = $_FILES['image_file']['type'] ?: null;
    if (!$mime && function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $tmpName);
            finfo_close($finfo);
        }
    }

    $allowed = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
    if ($mime && in_array($mime, $allowed, true)) {
        $bytes = @file_get_contents($tmpName);
        if ($bytes !== false) {
            return "data:$mime;base64," . base64_encode($bytes);
        }
    }
    return null;
}

/**
 * Process user input and call OpenAI API.
 */
function processUserInput($input_text): void {
    $openai_data = init_openai();
    $client = $openai_data[1];
    $selected_model = $_SESSION['model_choice'] ?? 'gpt-5-mini';
    $content_history = &$_SESSION['content_history'];

    $image_data_url = handleImageUpload();
    $myquestion = "QUESTION: " . $input_text . ($image_data_url ? " [image attached]" : "");
    $mycompletion = "ANSWER: " . get_openai_response_for_model($input_text, $selected_model, $client, $content_history, $image_data_url);
    
    $content_history[] = $myquestion;
    $content_history[] = $mycompletion;
    $_SESSION['content_history'] = $content_history;
    $_POST['input_text'] = '';
}

// Main logic
initSession();
$flash_error = detectUploadErrors();
$answeringOnLoad = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_button']));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['model_choice_chosen'])) {
        $_SESSION['model_choice'] = $_POST['model_choice_chosen'];
    }

    if (isset($_POST['submit_button']) && (!empty($_POST['input_text']) || (isset($_FILES['image_file']) && isset($_FILES['image_file']['error']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK))) {
        processUserInput($_POST['input_text'] ?? '');
    }

    if (isset($_POST['clear_history_button'])) {
        $_SESSION['content_history'] = [];
    }
}
?>

    <!DOCTYPE html>
    <html>

    <head>
        <title>ChatGPT client</title>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="chatbot.css">
    </head>

    <body>

    <?php if (!empty($flash_error)): ?>
        <div class="upload-error" role="alert" aria-live="assertive">
            <?php echo htmlspecialchars($flash_error); ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="controls-section">
            <span class="section-title">I want to chat:</span>
            <?php displayModelChoices(); ?>
            
            <?php $display_text = getDisplayText(); ?>
            <div class="input-container">
                <textarea name="input_text" id="input_text" class="input" rows="3" cols="40" style="width: 100%; box-sizing: border-box;"><?php echo $display_text; ?></textarea>
                <div class="voice-controls">
                    <span class="microphone-icon" title="Voice Input">
                        <svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor" style="vertical-align: middle;"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                    </span>
                    <button type="button" id="voiceInputBtn" class="voice-btn" title="Speak into microphone (English)">
                        <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#bd3d44" d="M0 0h640v480H0z"/><path fill="#fff" d="M0 0h640v44.3H0m0 88.6h640v44.3H0m0 88.6h640v44.3H0m0 88.6h640v44.3H0m0 88.6h640v44.3H0"/><path fill="#192f5d" d="M0 0h256v242.3H0z"/><path fill="#fff" d="M22.5 13.5l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm-182.8 38l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm-182.8 38l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm-182.8 38.1l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm-182.8 38l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2z"/></svg>
                    </button>
                    <button type="button" id="voiceInputBtnDe" class="voice-btn" title="Auf Deutsch sprechen">
                        <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#ffce00" d="M0 320h640v160H0z"/><path d="M0 0h640v160H0z"/><path fill="#d00" d="M0 160h640v160H0z"/></svg>
                    </button>
                    <button type="button" id="voiceInputBtnEs" class="voice-btn" title="Hablar en español">
                        <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#c60b1e" d="M0 0h640v120H0zm0 360h640v120H0z"/><path fill="#ffc400" d="M0 120h640v240H0z"/></svg>
                    </button>
                    <button type="button" id="voiceInputBtnFr" class="voice-btn" title="Parler en français">
                        <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#ed2939" d="M426.7 0H640v480H426.7z"/><path fill="#fff" d="M213.3 0h213.4v480H213.3z"/><path fill="#002395" d="M0 0h213.3v480H0z"/></svg>
                    </button>
                    <button type="button" id="voiceInputBtnIt" class="voice-btn" title="Parlare in italiano">
                        <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#009246" d="M0 0h213.3v480H0z"/><path fill="#fff" d="M213.3 0h213.4v480H213.3z"/><path fill="#ce2b37" d="M426.7 0H640v480H426.7z"/></svg>
                    </button>
                    <button type="button" id="voiceInputBtnNl" class="voice-btn" title="In het Nederlands spreken">
                        <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#21468b" d="M0 320h640v160H0z"/><path fill="#fff" d="M0 160h640v160H0z"/><path fill="#ae1c28" d="M0 0h640v160H0z"/></svg>
                    </button>
                    <button type="button" id="voiceInputBtnEl" class="voice-btn" title="Μιλήστε στα ελληνικά">
                        <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#005bae" d="M0 0h640v480H0z"/><path fill="#fff" d="M0 53.3h640v53.4H0M0 160h640v53.3H0M0 266.7h640v53.3H0M0 373.3h640v53.4H0"/><path fill="#005bae" d="M0 0h266.7v266.7H0z"/><path fill="#fff" d="M106.7 0h53.3v266.7h-53.3zM0 106.7h266.7v53.3H0z"/></svg>
                    </button>
                    <button type="button" id="voiceProcessBtn" class="voice-process-btn hidden" title="Process spoken text">Process</button>
                </div>
            </div>
            <div id="voiceStatus" class="voice-status hidden">Microphone ready...</div>
        </div>

        <div class="controls-section">
            <span class="section-title">Attach image (optional):</span>
            <input type="file" name="image_file" id="image_file" accept="image/*">
            <div id="imagePreviewWrapper" class="mt-2 hidden">
                <img id="imagePreview" alt="Selected image preview">
                <div id="imagePreviewNote" class="preview-note"></div>
            </div>
        </div>

        <div class="controls-section">
            <div class="button-group">
                <input type="submit" name="submit_button" class="ask green-background" value="ASK">
                <input type="submit" name="clear_history_button" class="ask red-background" value="NEW CHAT">
            </div>
        </div>
    </form>

    <div id="loadingOverlay" aria-live="polite" role="status" aria-busy="true">
        <div class="loading-content">
            <div class="loader" aria-hidden="true"></div>
            <div class="label">Working…</div>
        </div>
    </div>

    <div class="controls-section">
        <span class="section-title">Copy to clipboard</span>
        <div class="button-group">
            <button class="copy red-background" onclick="copyOutputToClipboard(event)">Chat</button>
            <button class="copy red-background" onclick="copyAnswerToClipboard(event)">Last answer</button>
        </div>
    </div>

    <div class="controls-section">
        <span class="section-title">Audio</span>
        <div class="button-group">
            <button type="button" id="playAudioBtn" class="voice-btn" title="Play Audio (English)" onclick="playAnswerAudio(event, 'en-US')">
                <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#bd3d44" d="M0 0h640v480H0z"/><path fill="#fff" d="M0 0h640v44.3H0m0 88.6h640v44.3H0m0 88.6h640v44.3H0m0 88.6h640v44.3H0m0 88.6h640v44.3H0"/><path fill="#192f5d" d="M0 0h256v242.3H0z"/><path fill="#fff" d="M22.5 13.5l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm-182.8 38l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm-182.8 38l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm-182.8 38.1l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm-182.8 38l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2zm45.7 0l3.8 11.6h12.2l-9.9 7.2 3.8 11.6-9.9-7.2-9.9 7.2 3.8-11.6-9.9-7.2h12.2z"/></svg>
            </button>
            <button type="button" id="playAudioBtnDe" class="voice-btn" title="Auf Deutsch abspielen" onclick="playAnswerAudio(event, 'de-DE')">
                <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#ffce00" d="M0 320h640v160H0z"/><path d="M0 0h640v160H0z"/><path fill="#d00" d="M0 160h640v160H0z"/></svg>
            </button>
            <button type="button" id="playAudioBtnEs" class="voice-btn" title="Reproducir en español" onclick="playAnswerAudio(event, 'es-ES')">
                <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#c60b1e" d="M0 0h640v120H0zm0 360h640v120H0z"/><path fill="#ffc400" d="M0 120h640v240H0z"/></svg>
            </button>
            <button type="button" id="playAudioBtnFr" class="voice-btn" title="Jouer en français" onclick="playAnswerAudio(event, 'fr-FR')">
                <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#ed2939" d="M426.7 0H640v480H426.7z"/><path fill="#fff" d="M213.3 0h213.4v480H213.3z"/><path fill="#002395" d="M0 0h213.3v480H0z"/></svg>
            </button>
            <button type="button" id="playAudioBtnIt" class="voice-btn" title="Riproduci in italiano" onclick="playAnswerAudio(event, 'it-IT')">
                <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#009246" d="M0 0h213.3v480H0z"/><path fill="#fff" d="M213.3 0h213.4v480H213.3z"/><path fill="#ce2b37" d="M426.7 0H640v480H426.7z"/></svg>
            </button>
            <button type="button" id="playAudioBtnNl" class="voice-btn" title="Afspelen in het Nederlands" onclick="playAnswerAudio(event, 'nl-NL')">
                <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#21468b" d="M0 320h640v160H0z"/><path fill="#fff" d="M0 160h640v160H0z"/><path fill="#ae1c28" d="M0 0h640v160H0z"/></svg>
            </button>
            <button type="button" id="playAudioBtnEl" class="voice-btn" title="Αναπαραγωγή στα ελληνικά" onclick="playAnswerAudio(event, 'el-GR')">
                <svg class="flag-icon" viewBox="0 0 640 480"><path fill="#005bae" d="M0 0h640v480H0z"/><path fill="#fff" d="M0 53.3h640v53.4H0M0 160h640v53.3H0M0 266.7h640v53.3H0M0 373.3h640v53.4H0"/><path fill="#005bae" d="M0 0h266.7v266.7H0z"/><path fill="#fff" d="M106.7 0h53.3v266.7h-53.3zM0 106.7h266.7v53.3H0z"/></svg>
            </button>
            <button id="stopAudioBtn" class="copy red-background" onclick="stopAnswerAudio(event)" disabled>Stop</button>
        </div>
    </div>
    <?php
    // Form submission already processed before HTML rendering
    ?>

    <?php
    if (!empty($_SESSION['content_history'])) {
        $history_count = count($_SESSION['content_history']);
        if ($history_count >= 2) {
            $last_question = $_SESSION['content_history'][$history_count - 2];
            $last_answer = $_SESSION['content_history'][$history_count - 1];
            ?>
            <div class="latest-interaction">
                <?php
                $display_question = (strpos($last_question, 'QUESTION: ') === 0) ? substr($last_question, 10) : $last_question;
                $display_answer = (strpos($last_answer, 'ANSWER: ') === 0) ? substr($last_answer, 8) : $last_answer;
                ?>
                <div class="question"><?php echo htmlspecialchars($display_question); ?></div>
                <div class="answer"><?php echo htmlspecialchars($display_answer); ?></div>
            </div>
            <?php
        }
    }
    ?>

    <div id="chatHistory">
    <?php
    if (!empty($_SESSION['content_history'])) {
        foreach ($_SESSION['content_history'] as $entry) {
            if (strpos($entry, 'QUESTION: ') === 0) {
                $text = htmlspecialchars(substr($entry, 10));
                ?>
                <div class="chat-bubble-container user">
                    <div class="chat-bubble user"><?php echo $text; ?></div>
                </div>
                <?php
            } elseif (strpos($entry, 'ANSWER: ') === 0) {
                $text = htmlspecialchars(substr($entry, 8));
                ?>
                <div class="chat-bubble-container ai">
                    <div class="chat-bubble ai"><?php echo $text; ?></div>
                </div>
                <?php
            }
        }
    } else {
        ?>
        <div class="no-history">No chat history yet. Start by asking a question above.</div>
        <?php
    }
    ?>
    </div>

    <?php
    // Output content history as text
    $content_history_text = implode("\n", $_SESSION['content_history']);
    ?>
    <textarea id="outputhistory" style="display:none;"><?php echo htmlentities($content_history_text); ?></textarea>

    <script src="chatbot.js"></script>
    <?php if (!empty($answeringOnLoad)): ?>
    <script>
        window.addEventListener('load', function() {
            if (typeof showAnswering === 'function') {
                showAnswering();
            }
        });
    </script>
    <?php endif; ?>
    <button id="scrollToTop" onclick="scrollToTop()">Top</button>
    </body>

    </html>

<?php

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

function getDisplayText(): string
{
    if (isset($_POST['submit_button']) && isset($_POST['input_text'])) {
        return htmlentities($_POST['input_text']);
    }
    return '';
}

?>