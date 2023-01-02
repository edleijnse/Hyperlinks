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
            font-size: 24px;
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
        .large-font {
            font-size: 72px;
        }
    </style>
</head>
<body>
<h3 class="heading">Formulieren Sie ihre Frage!</h3>
<form method="post">
    <label for="your_choice" class="list">Was möchten Sie?</label><br>
    <select name="your_choice" class="list" id="your_choice" size="8">
        <option value="" selected>Ich brauche keine Hilfe!</option>
        <option value="Gib mir eine Zusammenfassung des folgenden Textes:">Gib mir eine Zusammenfassung des folgenden Textes:</option>
        <option value="Dieser Text muss korrigiert werden:">Dieser Text muss korrigiert werden:</option>
        <option value="Übersetze diesen Text auf Deutsch:">Übersetze diesen Text auf Deutsch:</option>
        <option value="Übersetze diesen Text auf English:">Übersetze diesen Text auf English:</option>
        <option value="Übersetze diesen Text auf Spanisch:">Übersetze diesen Text auf Spanisch:</option>
        <option value="Übersetze diesen Text auf Französisch:">Übersetze diesen Text auf Französisch:</option>
        <option value="Übersetze diesen Text auf Italienisch:">Übersetze diesen Text auf Italienisch:</option>
    </select>
    <textarea name="input_text" class="input" rows="5" cols="50"></textarea>
    <br>
    <input type="submit" name="submit_button" class="ask" value="Gib mir eine Antwort!">
</form>
<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;


if (isset($_POST['submit_button'])) {
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
    // Close the file
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
    $input_text =  $your_choice . " " . $_POST['input_text'];

// The text prompt to send to the GPT-3 model
    $prompt = $input_text;
    // Set up the request body
    $requestBody = [
        'model' => 'text-davinci-003',
        'prompt' => $prompt,
        'max_tokens' => 512,
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

        echo "<br>";
        echo "<br>";
        echo "<p>";
        echo "<label for='question' class='large-font'>Question:</label><br>";
        echo "<textarea id='question' class='output' rows='5' cols='50'>$prompt</textarea>";
        echo "</p";
        echo "<br>";
        echo "<br>";
        echo "<p>";
        echo "<label for='output' class='large-font'>Answer:</label><br>";
        echo "<textarea id='output' class='output' rows='20' cols='50'>$completion</textarea>";
        echo "</p";
    } catch (Exception $e) {
        // An error occurred, print the error message
        echo "Error occurred: " . $e->getMessage();
    }
}
?>
</body>
</html>
