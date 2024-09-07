<!DOCTYPE html>
<html>
<?php include 'head.php'; ?>
<body>
<?php echo "<ul>"; ?>
<h3 class="ask">chat client for OpenAI</h3>
<p>
<form method="post">
    <!--<label class="ask" for="model_choice">using model:</label>
    <select name="model_choice" class="ask" id="model_choice">
       <option value="gpt4">GPT4</option>
        <option value="gpt4o-mini" selected>GPT4o-mini</option>
    </select> -->

    <br>
    <?php
    session_start();
    $_SESSION['model_choice']="gpt4o-mini";

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
<button class="copy-all-button" onclick="copyOutputToClipboard()" style="font-size:40px; padding:10px;">all to clipboard</button>
<button class="copy-answer-button" onclick="copyAnswerToClipboard()" style="font-size:40px; padding:10px;">answer to clipboard</button>

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
        // $selected_model = $_POST['model_choice'];
        $selected_model = $_SESSION['model_choice'];
        $content_history = &$_SESSION['content_history']; // Reference to session variable

        if ($selected_model === 'gpt') {
            get_openai_response($input_text, $client);
        } elseif ($selected_model === 'gpt4') {
            $myquestion = "QUESTION: " . $input_text;
            $mycompletion =  "ANSWER: " . get_openai_response_gpt4($input_text, $content_history, $client);
            $content_history[] = $myquestion;
            $content_history[] = $mycompletion;
        } elseif ($selected_model === 'gpt4o-mini') {
            $myquestion = "QUESTION: " . $input_text;
            $mycompletion =  "ANSWER: " . get_openai_response_gpt4omini($input_text, $content_history, $client);
            $content_history[] = $myquestion;
            $content_history[] = $mycompletion;
        } else {
            $mycompletion = "Question: " . $input_text . " " . "Answer: " .  get_openai_response_gpt4($input_text, $content_history, $client);
            $content_history[] = $mycompletion;
        }

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
    function copyOutputToClipboard() {
        const outputText = document.getElementById("outputhistory").value;

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
<script>
    function copyAnswerToClipboard() {
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