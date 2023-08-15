<!DOCTYPE html>
<html>
<?php include 'head.php'; ?>
<body>
<h2 class="ask">Enter your question here</h2>
<p>
<form method="post">
    <label class="ask" for="model_choice">Choose a model:</label>
    <select name="model_choice" class="ask" id="model_choice">
        <option value="gpt">GPT</option>
        <option value="gpt4" selected>GPT4</option>
    </select>
    <br>
    <textarea name="input_text" class="input" rows="5" cols="50">
        <?php if (isset($_POST['input_text'])) { echo htmlentities($_POST['input_text']); } else { echo ""; } ?>
    </textarea>
    <br>
    <input type="submit" name="submit_button" class="ask" value="Ask me anything">
</form>
</p>
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
        if ($selected_model === 'gpt') {
            get_openai_response($input_text, $client);
        } elseif ($selected_model === 'gpt4') {
            get_openai_response_gpt4($input_text, $client);
        } else {
            get_openai_response_gpt4($input_text, $client);
        }
    }
}
?>
</body>
</html>
