<?php
session_start();
require 'vendor/autoload.php';
require 'openai_functions.php';

use GuzzleHttp\Client;

?>

    <!DOCTYPE html>
    <html>

    <head>
        <?php include 'head.php'; ?>
        <style>
            .large-font {
                font-size: 34px;
            }
        </style>
    </head>

    <body>
    <?php echo "<ul>"; ?>
    <h3 class="ask">Chat client for OpenAI</h3>
    <p>
    <form method="post">
        <label class="ask large-font" for="model_choice">I want to chat:<br></label>

        <?php
        displayModelChoices();
        ?>
        <?php
           $display_text = getDisplayText();
         ?>
        <textarea name="input_text" class="input" rows="3" cols="40"><?php echo $display_text; ?></textarea>
        <br>
        <input type="submit" name="submit_button" class="ask red-background" value="ASK">
        <input type="submit" name="clean_button" class="ask red-background" value="NEXT">
        <input type="submit" name="clear_history_button" class="ask green-background" value="NEW CHAT">
    </form>
    </p>

    <span style="font-size: 40px;">Copy to clipboard</span>
    <br>
    <button class="copy-all-button red-background" onclick="copyOutputToClipboard(event)"
            style="font-size:40px; padding:10px;">Complete chat
    </button>
    <button class="copy-answer-button red-background" onclick="copyAnswerToClipboard(event)"
            style="font-size:40px; padding:10px;">Last answer
    </button>
    <?php

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handleFormSubmission();
    }

    initializeContentHistory();

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
    </body>

    </html>

<?php

function handleFormSubmission()
{
    if (isset($_POST['model_choice_chosen'])) {
        $_SESSION['model_choice'] = $_POST['model_choice_chosen'];
    }

    if (isset($_POST['submit_button']) && !empty($_POST['input_text'])) {
        processUserInput($_POST['input_text']);
    }

    if (isset($_POST['clean_button'])) {
        cleanInputText();
    }

    if (isset($_POST['clear_history_button'])) {
        clearContentHistory();
    }
}

function displayModelChoices()
{
    $selected_model = isset($_SESSION['model_choice']) ? $_SESSION['model_choice'] : 'gpt-4o-mini';

    echo generateRadioOption('gpt-4o-mini', 'simple and fast using model gpt-4o-mini', $selected_model);
    echo generateRadioOption('gpt-4o', 'more accurate but slower using model gpt-4o', $selected_model);
}

function generateRadioOption($id, $label, $selected_model)
{
    $checked = $selected_model === $id ? 'checked' : '';
    return <<<HTML
    <input type="radio" id="$id" name="model_choice_chosen" value="$id" $checked class="large-font">
    <label for="$id" class="large-font">$label</label><br>
HTML;
}

function initializeContentHistory()
{
    if (!isset($_SESSION['content_history'])) {
        $_SESSION['content_history'] = [];
    }
}

function getDisplayText()
{
    if (isset($_POST['submit_button'])) {
        return htmlentities($_POST['input_text']);
    }
    return '';
}

function processUserInput($input_text)
{
    $openai_data = init_openai();
    $client = $openai_data[1];
    $selected_model = $_SESSION['model_choice'] ?: 'gpt-4o-mini';
    $content_history = &$_SESSION['content_history'];

    $myquestion = "QUESTION: " . $input_text;
    // in openai_functions all output is generated
    $mycompletion = "ANSWER: " . get_openai_response_for_model($input_text, $selected_model, $content_history, $client);
    $content_history[] = $myquestion;
    $content_history[] = $mycompletion;

    $_SESSION['content_history'] = $content_history;
}

function cleanInputText()
{
    $_POST['input_text'] = '';
}

function clearContentHistory()
{
    $_SESSION['content_history'] = [];
}

?>