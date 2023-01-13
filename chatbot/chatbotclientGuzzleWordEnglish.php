<!DOCTYPE html>
<html>
<head>
    <title>ChatGPT client</title>
    <style>
        .input {
            font-size: 24px;
            color: royalblue;
        }
        .list {
            font-size: 38px;
            color: goldenrod;
        }
        .output {
            font-size: 24px;
            color: dimgray;
        }
        .ask {
            font-size: 64px;
        }
        .heading {
            font-size: 38px;
        }
        .error-message {
            font-size: 38px;
        }
        .large-font {
            font-size: 72px;
        }
    </style>
</head>
<body>
<a href="https://tagger.biz/chatbot/chatbotclientGuzzle.php/" class="heading">Home</a>
<h3 class="heading">Enter your question!</h3>
<form method="post">
    <label for="your_choice" class="list">Tell me what you want</label><br>
    <select name="your_choice" class="list" id="your_choice" size="8">
        <option value="" selected>No need for help. Otherwise choose below</option>
        <option value="Make a summary of the following text:">Make a summary of the following text:</option>
        <option value="Correct this text:">Correct this text:</option>
        <option value="Translate in German:">Translate in German:</option>
        <option value="Translate in English:">Translate in English:</option>
        <option value="Translate in Spanish:">Translate in Spanish:</option>
        <option value="Translate in French:">Translate in French:</option>
        <option value="Translate in Italian:">Translate in Italian:</option>
    </select>
    <br>
    <br>
    <textarea name="input_text" class="input" rows="5" cols="50"><?php echo htmlspecialchars($_POST['input_text']); ?></textarea>
    <br>
    <br>
    <input type="submit" name="submit_button" class="ask" value="Answer me!">
</form>
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
        $input_text = $your_choice . " " . $_POST['input_text'];
        // The text prompt to send to the GPT-3 model
        get_openai_response($input_text, $client);
    }
}
?>
</body>
</html>
