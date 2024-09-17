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
<h3 class="ask">chat client for OpenAI</h3>
<p>
<form method="post">
    <label class="ask large-font" for="model_choice">I want to chat:<br></label>

    <?php
    session_start();

    // Check if a choice has been submitted
    if (isset($_POST['model_choice_chosen'])) {
        $_SESSION['model_choice'] = $_POST['model_choice_chosen'];
    }

    // Set selected model or default to 'gpt-4o-mini'
    $selected_model = $_SESSION['model_choice'] ?? 'gpt-4o-mini';
    ?>

    <input type="radio" id="gpt-4o-mini" name="model_choice_chosen" value="gpt-4o-mini"
        <?php echo $selected_model === 'gpt-4o-mini' ? 'checked' : ''; ?> class="large-font">
    <label for="gpt-4o-mini" class="large-font">simple and fast using model gpt-4o-mini</label><br>

    <input type="radio" id="gpt-4o" name="model_choice_chosen" value="gpt-4o"
        <?php echo $selected_model === 'gpt-4o' ? 'checked' : ''; ?> class="large-font">
    <label for="gpt-4o" class="large-font">more accurate but slower using model gpt-4o</label><br>

    <?php
    // Initialize content history session variable if not set
    if (!isset($_SESSION['content_history'])) {
        $_SESSION['content_history'] = [];
    }

    $display_text = '';
    if (isset($_POST['submit_button'])) {
        $display_text = htmlentities($_POST['input_text']);
    }
    if (isset($_POST['clean_button'])) {
        $display_text = '';
    }
    if (isset($_POST['clear_history_button'])) {
        $_SESSION['content_history'] = []; // Clear content history
    }
    echo "<ul>";
    ?>
    <textarea name="input_text" class="input" rows="3" cols="40"><?php echo $display_text; ?></textarea>
    <br>
    <input type="submit" name="submit_button" class="ask red-background" value="      ASK      ">
    <input type="submit" name="clean_button" class="ask red-background" value="  NEW QUESTION  ">
    <input type="submit" name="clear_history_button" class="ask green-background" value=" NEW CHAT ">
</form>
</p>
<span style="font-size: 40px;">copy to clipboard</span>
<br>
<button class="copy-all-button red-background" onclick="copyOutputToClipboard(event)" style="font-size:40px; padding:10px;">complete chat</button>
<button class="copy-answer-button red-background" onclick="copyAnswerToClipboard(event)" style="font-size:40px; padding:10px;">last answer</button>

<?php
require 'vendor/autoload.php';
require 'openai_functions.php';

use GuzzleHttp\Client;

$openai_data = init_openai();
$your_choice = $openai_data[0];
$client = $openai_data[1];

if (isset($_POST['submit_button'])) {
    if (empty($_POST['input_text'])) {
        echo '<p class="error-message">Please enter your question</p>';
    } else {
        // Load the Guzzle library
        // Get the input text
        $input_text = $_POST['input_text'];

        // Determine the selected model choice
        $selected_model = $selected_model; // Using the session-stored model
        $content_history = &$_SESSION['content_history']; // Reference to session variable
        $myquestion = "QUESTION: " . $input_text;
        $mycompletion =  "ANSWER: " . get_openai_response_for_model($input_text, $selected_model, $content_history, $client);
        $content_history[] = $myquestion;
        $content_history[] = $mycompletion;
        // Save updated content history back to session
        $_SESSION['content_history'] = $content_history;
    }
}

// Output content history as text
$content_history_text = implode("\n", $_SESSION['content_history']);
?>
<!-- Hidden textarea for content history -->
<textarea id="outputhistory" style="display:none;"><?php echo htmlentities($content_history_text); ?></textarea>
<!-- Add the "Copy" button -->
<br>

<script>
    function copyOutputToClipboard(event) {
        event.preventDefault(); // Prevent form submission
        const outputText = document.getElementById("outputhistory").value;
        navigator.clipboard.writeText(outputText).then(() => {
            alert("Copied to clipboard: " + outputText);
        }).catch(err => {
            console.error('Could not copy text: ', err);
        });
    }

    function copyAnswerToClipboard(event) {
        event.preventDefault(); // Prevent form submission
        const outputText = document.getElementById("outputhistory").value;
        var lastIndex = outputText.lastIndexOf("ANSWER: ");
        if (lastIndex !== -1) {
            // Calculate the start index for selection
            var startIndex = lastIndex + "ANSWER: ".length;

            // Select the text after the last occurrence of "ANSWER: "
            var endText = outputText.substring(startIndex);

            // Create a temporary textarea element
            const tempTextarea = document.createElement("textarea");
            tempTextarea.value = endText;
            document.body.appendChild(tempTextarea);

            // Select the text inside the temporary textarea
            tempTextarea.select();
            tempTextarea.setSelectionRange(0, 99999); // For mobile devices

            // Copy the text to the clipboard
            document.execCommand("copy");

            // Clean up by removing the temporary textarea
            document.body.removeChild(tempTextarea);

            // Show an alert to indicate successful copy
            alert("Copied to clipboard: " + endText);

        }
    }
</script>

</body>
</html>