<!DOCTYPE html>
<html>
<?php include 'head.php'; ?>
<body>
<a href="/chatbot/chatbotclientGuzzle.php/" class="heading">Home</a>
<h3 class="heading">¿Ingresa tu pregunta?</h3>
<form method="post">
    <label for="your_choice" class="list">Dime lo que quieres</label><br>
    <select name="your_choice" class="list" id="your_choice" size="8">
        <option value="" selected>No hay necesidad de ayuda. De lo contrario, elija a continuación.</option>
        <option value="Haga un resumen del siguiente texto:">Haga un resumen del siguiente texto:</option>
        <option value="Corrige este texto:">Corrige este texto:</option>
        <option value="Traducir al alemán:">Traducir al alemán:</option>
        <option value="Traducir al inglés:">Traducir al inglés:</option>
        <option value="Traducir al español:">Traducir al español:</option>
        <option value="Traducir al francés:">Traducir al francés:</option>
        <option value="Traducir al italiano:">Traducir al italiano:</option>
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
    <input type="submit" name="submit_button" class="ask" value="Respóndeme!">
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
        echo '<p class="error-message">Por favor ingresa tu pregunta.</p>';
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
