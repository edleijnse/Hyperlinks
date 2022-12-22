<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
$api_key = "sk-YOUR KEY";
// Set up the client
$client = new Client([
    'base_uri' => 'https://api.openai.com',
    'headers' => [
        "Authorization" => "Bearer $api_key",
        'Content-Type' => 'application/json',
    ]
]);

// Set up the request body
$requestBody = [
    'model' => 'text-davinci-002',
    'prompt' => 'What is the capital of France?',
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

echo $completion;


