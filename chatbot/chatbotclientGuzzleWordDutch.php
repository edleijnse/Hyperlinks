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
<h3 class="heading">Stel je vraag!</h3>
<form method="post">
    <label for="your_choice" class="list">Vertel mij wat je wilt</label><br>
    <select name="your_choice" class="list" id="your_choice" size="9">
        <option value="" selected>Geen hulp nodig. Maak uw keuze hier onder</option>
        <option value="aak een samenvatting van deze tekst:">Maak een samenvatting van deze tekst:</option>
        <option value="Korrigeer deze tekst:">Korrigeer deze tekst:</option>
        <option value="Vertaal in het Nederlands:">Vertaal in het Nederlands:</option>
        <option value="Vertaal in het Engels:">Vertaal in het Engels:</option>
        <option value="Vertaal in het Spaans:">Vertaal in het Spaans:</option>
        <option value="Vertaal in het Frans:">Vertaal in het Frans:</option>
        <option value="Vertaal in het Italiaans:">Vertaal in het Italiaans:</option>
        <option value="Vertaal in het Duits:">Vertaal in het Duits:</option>
    </select>
    <br>
    <br>
    <textarea name="input_text" class="input" rows="5" cols="50"><?php echo htmlspecialchars($_POST['input_text']); ?></textarea>
    <br>
    <br>
    <input type="submit" name="submit_button" class="ask" value="Antwoord mij!">
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
        echo '<p class="error-message">Vraag ingeven a.u.b.</p>';
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
