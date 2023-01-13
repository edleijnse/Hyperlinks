<!DOCTYPE html>
<html>
<?php include 'head.php'; ?>
<body>
<a href="https://tagger.biz/chatbot/chatbotclientGuzzleWordEnglish.php/" class="heading">English</a>
<sp>__</sp>
<a href="https://tagger.biz/chatbot/chatbotclientGuzzleWordGerman.php/" class="heading">German</a>
<sp>__</sp>
<a href="https://tagger.biz/chatbot/chatbotclientGuzzleWordSpanish.php/" class="heading">Spanish</a>
<sp>__</sp>
<a href="https://tagger.biz/chatbot/chatbotclientGuzzleWordDutch.php/" class="heading">Dutch</a>
<sp>__</sp>
<a href="https://tagger.biz/2023/01/04/chatgpt-client-user-manual/" class="heading">HELP</a>
<h3 class="heading">Enter your question here</h3>
<form method="post">
    <textarea name="input_text" class="input" rows="5" cols="50"><?php echo htmlspecialchars($_POST['input_text']); ?></textarea>
    <br>
    <input type="submit" name="submit_button" class="ask" value="Ask me anything">
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
        echo '<p class="error-message">Please enter your questions</p>';
    } else {
        // Load the Guzzle library
        // Get the input text
        $input_text = $_POST['input_text'];
        get_openai_response($input_text, $client);
    }
}
?>
</body>
</html>
