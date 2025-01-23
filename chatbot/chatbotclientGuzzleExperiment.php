<?php
// session_start();
require 'vendor/autoload.php';
require 'openai_functionsExperiment.php';

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
            .header-font {
                font-size: 24px;
            }
            /* Add CSS here */
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
        </style>
    </head>

    <body>
    <?php echo "<ul>"; ?>

    <form method="post">
        <label for="model_choice">I want to chat:<br></label>

        <?php
        displayModelChoices();
        ?>
        <?php
           $display_text = getDisplayText();
         ?>
        <textarea name="input_text" rows="3" cols="40"><?php echo $display_text; ?></textarea>
        <br>
        <input type="submit" name="submit_button" value="ASK">
        <input type="submit" name="clean_button"  value="NEXT">
        <input type="submit" name="clear_history_button" value="NEW CHAT">
    </form>
    </p>

    <span>Copy to clipboard</span>
    <br>
    <button  onclick="copyOutputToClipboard(event)">Complete chat
    </button>
    <button onclick="copyAnswerToClipboard(event)">Last answer
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
    <!-- Add the button -->
    <button id="scrollToTop" onclick="scrollToTop()">Top</button>

    <!-- Add JS here -->
    <script>
        // Scroll to the top of the page when the button is clicked
        function scrollToTop() {
            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
        }
    </script>
    </body>

    </html>

<?php

function handleFormSubmission(): void
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

function displayModelChoices(): void
{
    $selected_model = isset($_SESSION['model_choice']) ? $_SESSION['model_choice'] : 'gpt-4o-mini';
    echo "<br>";
    echo generateRadioOption('gpt-4o-mini', 'simple and fast using model gpt-4o-mini', $selected_model);
    echo generateRadioOption('gpt-4o', 'more accurate but slower using model gpt-4o', $selected_model);
}

function generateRadioOption($id, $label, $selected_model): string
{
    $checked = $selected_model === $id ? 'checked' : '';
    return <<<HTML
    <input type="radio" id="$id" name="model_choice_chosen" value="$id" $checked>
    <label for="$id">$label</label><br>
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
    $selected_model = $_SESSION['model_choice'] ?: 'gpt-4o-mini';
    $content_history = &$_SESSION['content_history'];

    $myquestion = "QUESTION: " . $input_text;
    // in openai_functions all output is generated
    $mycompletion = "ANSWER: " . get_openai_response_for_model($input_text, $selected_model, $content_history, $client);
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