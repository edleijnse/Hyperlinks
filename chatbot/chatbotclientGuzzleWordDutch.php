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
        <option value="Maak een samenvatting van deze tekst:">Maak een samenvatting van deze tekst:</option>
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

use GuzzleHttp\Client;


if (isset($_POST['submit_button'])) {
    if (empty($_POST['input_text'])) {
        echo '<p class="error-message">Vraag ingeven a.u.b.</p>';
    } else {
        $your_choice = $_POST['your_choice'];
        // Your OpenAI API key from a file
        // Get the current working directory
        $current_dir = getcwd();
        // Get the directory one level above the current working directory
        $parent_dir = dirname($current_dir);
        // Open the file located in the parent directory
        $file = fopen($parent_dir . '/api_key', 'r');
        // Read the contents of the file
        $api_key = fread($file, filesize($parent_dir . '/api_key'));
        // Close the file<option value="Translate in Italian:">Vertaal in het Italiaans:</option>
        fclose($file);
        // Remove the end of line characters from the contents
        $api_key = str_replace(array("\r", "\n"), '', $api_key);

        // Set up the client
        $client = new Client([
            'base_uri' => 'https://api.openai.com',
            'headers' => [
                "Authorization" => "Bearer $api_key",
                'Content-Type' => 'application/json',
            ]
        ]);
        // Load the Guzzle library
        // Get the input text
        $input_text = $your_choice . " " . $_POST['input_text'];

// The text prompt to send to the GPT-3 model
        $prompt = $input_text;
        // Set up the request body
        $requestBody = [
            'model' => 'text-davinci-003',
            'prompt' => $prompt,
            'max_tokens' => 2048,
        ];
        // Make the request
        try {
            $response = $client->post('/v1/completions', [
                'body' => json_encode($requestBody)
            ]);
            // Get the response body as a string
            $responseBody = $response->getBody()->getContents();
// Decode the JSON response
            $responseData = json_decode($responseBody, true);

// Access the completion text
            $completion = $responseData['choices'][0]['text'];
            # echo "<p>Answer: </p>";
            # echo $completion;

            echo "<p>";
            echo "<label for='output' class='large-font'>Answer:</label><br>";
            echo "<textarea id='output' class='output' rows='20' cols='50'>$completion</textarea>";
            echo "</p";
        } catch (Exception $e) {
            // An error occurred, print the error message
            echo "Error occurred: " . $e->getMessage();
        }
    }
}
?>
</body>
</html>
