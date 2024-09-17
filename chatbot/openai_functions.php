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


function get_openai_response_for_model($input_text, $model, $content_history = [], $client)
{
    // echo "<p>..<em>waiting..</em></p>";
    // Set up the request body
    $user = 'language teacher English, Spanish and German';

    // Prepare message history by appending the current input
    $messages = array_map(function ($content) {
        return ['role' => 'user', 'content' => $content];
    }, $content_history);

    // Add the current input to the messages.
    $messages[] = ['role' => 'user', 'content' => $input_text];

    $requestBody = [
        'model' => $model,
        'messages' => $messages,
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
        $average_chars_per_row = 30;
        $rows = ceil(strlen($input_text) / $average_chars_per_row);
        // $rows = $rows + 1;

        echo "<p>";
        echo "<label for='output' class='large-font'>Question:</label><br>";
        echo "<textarea id='output textarea-no-interaction' class='output' rows='{$rows}' cols='40' readonly>$input_text</textarea>";
        echo "</p>";
        $rows = ceil(strlen($completion) / $average_chars_per_row);
        echo "<p>";
        echo "<label for='output' class='large-font'>Answer:</label><br>";
        echo "<textarea id='output' class='output textarea-no-interaction'  rows='{$rows}' cols='40' readonly>$completion</textarea>";
        echo "</p>";
        // Echo the content history
        if (!empty($content_history)) {
            echo "<span style='font-size: 40px;'>previous questions</span><br>";

            foreach ($content_history as $history_item) {
                $average_chars_per_row = 40;
                $rows = ceil(strlen($history_item) / $average_chars_per_row);
                $rows = $rows + 1;
                echo "<textarea class='output textarea-no-interaction' rows='{$rows}' cols='40' readonly>" . htmlentities($history_item) . "</textarea>";
                echo "<br>";
                // echo "<li class='medium-font'>" . htmlentities($history_item) . "</li>";
            }
        }

        return $completion;
    } catch (Exception $e) {
        // An error occurred, print the error message
        echo "Error occurred: " . $e->getMessage();
    }


    echo "<style>
.textarea-no-interaction {
    pointer-events: none;        /* Disable user interaction */
    user-select: none;           /* Disable text selection */
    -webkit-user-select: none;   /* Safari and Chrome */
    -moz-user-select: none;      /* Firefox */
    -ms-user-select: none;       /* IE 10+ */
}
</style>";
}

?>