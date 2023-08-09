<!DOCTYPE html>
<html>
<?php include 'head.php'; ?>
<body>
<a href="/chatbot/chatbotclientGuzzleGPT4.php/" class="heading">Home</a>
<h3 class="heading">Formulieren Sie ihre Frage!</h3>
<form method="post">
    <label for="your_choice" class="list">Was möchten Sie?</label><br>
    <select name="your_choice" class="list" id="your_choice" size="8">
        <option value="" selected>Keine Hilfe nötig. Ansonsten wähle hier unten.</option>
        <option value="Gib mir eine Zusammenfassung des folgenden Textes:">Gib mir eine Zusammenfassung des folgenden Textes:</option>
        <option value="Dieser Text muss korrigiert werden:">Dieser Text muss korrigiert werden:</option>
        <option value="Übersetze diesen Text auf Deutsch:">Übersetze diesen Text auf Deutsch:</option>
        <option value="Übersetze diesen Text auf English:">Übersetze diesen Text auf English:</option>
        <option value="Übersetze diesen Text auf Spanisch:">Übersetze diesen Text auf Spanisch:</option>
        <option value="Übersetze diesen Text auf Französisch:">Übersetze diesen Text auf Französisch:</option>
        <option value="Übersetze diesen Text auf Italienisch:">Übersetze diesen Text auf Italienisch:</option>
    </select>
    <br>
    <br>
    <textarea name="input_text" class="input" rows="5" cols="50">
        <?php if (isset($_POST['input_text'])) {
            echo htmlentities($_POST['input_text']);
        } else {
            echo "";
        } ?></textarea>
    <br>
    <br>
    <input type="submit" name="submit_button" class="ask" value="Gib mir eine Antwort!">
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
        echo '<p class="error-message">Bitte Frage eingeben.</p>';
    } else {

        // Load the Guzzle library
        // Get the input text
        $input_text = $your_choice . " " . $_POST['input_text'];
        // The text prompt to send to the GPT-3 model
        get_openai_response_gpt4($input_text, $client);
    }
}
?>
</body>
</html>
