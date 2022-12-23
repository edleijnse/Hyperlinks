<!DOCTYPE html>
<html>
<head>
    <title>PHP ChatGPT example</title>
</head>
<body>
<form method="post">
    <textarea name="input_text" rows="5" cols="50"></textarea>
    <br>
    <input type="submit" name="submit_button" value="Ask me anything">
</form>
<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;


if (isset($_POST['submit_button'])) {
    // Your OpenAI API key
    $api_key = "";
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
    $input_text = $_POST['input_text'];

// The text prompt to send to the GPT-3 model
    $prompt = $input_text;
    // Set up the request body
    $requestBody = [
        'model' => 'text-davinci-002',
        'prompt' => $prompt,
        'max_tokens' => 256,
    ];
    // Make the request
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
    echo "<label for='output'>Answer:</label><br>";
    echo "<textarea id='output' rows='5' cols='50'>$completion</textarea>";

}
?>
</body>
</html>
