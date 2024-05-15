<?php

use GuzzleHttp\Client;

function init_openai()
{
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
    //Set value of $your_choice
    $your_choice = "";
    if (isset($_POST['your_choice'])) {
        $your_choice = $_POST['your_choice'];
    }
    // Set up the client
    $client = new Client([
        'base_uri' => 'https://api.openai.com',
        'headers' => [
            "Authorization" => "Bearer $api_key",
            'Content-Type' => 'application/json',
        ],
    ]);
    return array($your_choice, $client);
}


function get_openai_response($input_text, $client) {
    $prompt = $input_text;
    // Set up the request body
    $user = 'language teacher English, Spanish and German';
    $requestBody = [
        'model' => 'text-davinci-003',
        'prompt' => $prompt,
        'temperature' => 0.0,
        'max_tokens' => 2048,
        'user' => $user,
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
        echo "<p>";
        echo "<label for='output' class='large-font'>Answer:</label><br>";
        echo "<textarea id='output' class='output' rows='20' cols='50'>$completion</textarea>";
        echo "</p";
    } catch (Exception $e) {
        // An error occurred, print the error message
        echo "Error occurred: " . $e->getMessage();
    }
}

function get_openai_response_gpt4($input_text, $client) {
    // Set up the request body
    $user = 'language teacher English, Spanish and German';
    $requestBody = [
        'model' => 'gpt-4o',
        'messages' => [
            ['role' => 'user', 'content' => $input_text],
        ],
        //'temperature' => 0.0,
        // 'max_tokens' => 2048,
        //'user' => $user,
    ];
    // Make the request
    try {
        $response = $client->post('/v1/chat/completions', [
            'body' => json_encode($requestBody)
        ]);
        // Get the response body as a string
        $responseBody = $response->getBody()->getContents();
        // Decode the JSON response
        $responseData = json_decode($responseBody, true);
        // Access the completion text
        $completion = $responseData['choices'][0]['message']['content'];
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