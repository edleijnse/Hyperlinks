<!DOCTYPE html>
<html>
<?php include 'head.php'; ?>
<body>
<h3 class="ask">Enter your question here</h3>
<p>
<form method="post">
    <label class="ask" for="model_choice">using model:</label>
    <select name="model_choice" class="ask" id="model_choice">
        <option value="gpt4">GPT4</option>
        <option value="gpt4o-mini" selected>GPT4o-mini</option>
    </select>
    <br>
    <?php
    session_start();

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
    ?>
    <textarea name="input_text" class="input" rows="5" cols="50"><?php echo $display_text ?></textarea>
    <br>
    <?php if (empty($display_text)): // Check if the variable is empty ?>
        <p class="blink-text">Please wait 5 secs after pressing "Ask me anything"</p> <!-- Add your text here -->
    <?php endif; ?>
    <input type="submit" name="submit_button" class="ask" value="Ask me anything">
    <input type="submit" name="clean_button" class="ask" value="Clean Input">
    <input type="submit" name="clear_history_button" class="ask" value="Clear History">
</form>
</p>
<button class="copy-button" onclick="copyOutputToClipboard()" style="font-size:30px; padding:10px;">copy answer to clipboard</button>
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
        $selected_model = $_POST['model_choice'];
        $content_history = &$_SESSION['content_history']; // Reference to session variable

        if ($selected_model === 'gpt') {
            get_openai_response($input_text, $client);
        } elseif ($selected_model === 'gpt4') {
            $mycompletion = get_openai_response_gpt4($input_text, $client);
            $content_history[] = $mycompletion;
        } elseif ($selected_model === 'gpt4o-mini') {
            $mycompletion = "Question: " . $input_text . " " . "Answer: " .  get_openai_response_gpt4omini($input_text, $content_history, $client);

            $content_history[] = $mycompletion;
        } else {
            $mycompletion = get_openai_response_gpt4($input_text, $client);
            $content_history[] = $mycompletion;
        }

        // Save updated content history back to session
        $_SESSION['content_history'] = $content_history;
    }
}
?>
<!-- Add the "Copy" button -->
<br>

<script>
    function copyOutputToClipboard() {
        const outputText = document.getElementById("output").value;

        // Create a temporary textarea element
        const tempTextarea = document.createElement("textarea");
        tempTextarea.value = outputText;
        document.body.appendChild(tempTextarea);

        // Select the text inside the temporary textarea
        tempTextarea.select();
        tempTextarea.setSelectionRange(0, 99999); // For mobile devices

        // Copy the text to the clipboard
        document.execCommand("copy");

        // Clean up by removing the temporary textarea
        document.body.removeChild(tempTextarea);

        // Show an alert to indicate successful copy
        alert("Copied to clipboard: " + outputText);
    }
</script>
</body>
</html>