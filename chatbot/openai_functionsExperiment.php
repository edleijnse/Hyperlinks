<?php

use GuzzleHttp\Client;

/**
 * Initialize the OpenAI client.
 *
 * @return array [$choice, $client]
 */
function init_openai()
{
    $api_key = get_api_key();
    $client = create_client($api_key);

    $user_choice = $_POST['your_choice'] ?? '';

    return [$user_choice, $client];
}

/**
 * Get OpenAI API key from a file.
 *
 * @return string
 */
function get_api_key()
{
    $api_key_path = dirname(getcwd()) . '/api_key';
    $api_key = trim(file_get_contents($api_key_path));

    return $api_key;
}

/**
 * Create a Guzzle HTTP client configured for OpenAI.
 *
 * @param string $api_key
 * @return Client
 */
function create_client($api_key)
{
    return new Client([
        'base_uri' => 'https://api.openai.com',
        'headers' => [
            "Authorization" => "Bearer $api_key",
            'Content-Type' => 'application/json',
        ],
    ]);
}

/**
 * Get a response from the OpenAI API using the specified model.
 *
 * @param string $input_text
 * @param string $model
 * @param array $content_history
 * @param Client $client
 * @return string|null
 */
function get_openai_response_for_model($input_text, $model, Client $client, array $content_history = [])
{
    $messages = prepare_messages($input_text, $content_history);
    $requestBody = ['model' => $model, 'messages' => $messages];
    $response = make_request($client, $requestBody);
    $completion = $response['choices'][0]['message']['content'] ?? null;

    display_interaction($input_text, $completion, $content_history);

    return $completion;
}

/**
 * Prepare message history by appending the current input.
 *
 * @param string $input_text
 * @param array $content_history
 * @return array
 */
function prepare_messages($input_text, array $content_history)
{
    $messages = array_map(function ($content) {
        return ['role' => 'user', 'content' => $content];
    }, $content_history);

    $messages[] = ['role' => 'user', 'content' => $input_text];

    return $messages;
}

/**
 * Make a request to the OpenAI API.
 *
 * @param Client $client
 * @param array $requestBody
 * @return array
 */
function make_request(Client $client, array $requestBody)
{
    try {
        $response = $client->post('/v1/chat/completions', ['body' => json_encode($requestBody)]);
        $responseBody = $response->getBody()->getContents();

        return json_decode($responseBody, true);
    } catch (Exception $e) {
        echo "Error occurred: " . $e->getMessage();
        return [];
    }
}

/**
 * Display the interaction and content history.
 *
 * @param string $input_text
 * @param string $completion
 * @param array $content_history
 * @return void
 */
function display_interaction($input_text, $completion, array $content_history)
{
    $average_chars_per_row = 20;

    echo generate_textarea('Question:', $input_text, $average_chars_per_row, 'lightyellow');

    if ($completion) {
        echo generate_textarea('Answer:', $completion, $average_chars_per_row, 'lightyellow');
    }

    if ($content_history) {
        echo "<span style='font-size: 40px;'>Previous questions</span><br>";

        foreach ($content_history as $history_item) {
            echo generate_textarea('', htmlentities($history_item), $average_chars_per_row, 'lightblue' );
        }
    }

    echo get_textarea_style();
}

/**
 * Generate a textarea with the specified content.
 *
 * @param string $label
 * @param string $content
 * @param int $average_chars_per_row
 * @return string
 */
/**
 * Generate a textarea with the specified content.
 *
 * @param string $label
 * @param string $content
 * @param int $average_chars_per_row
 * @param string|null $background Optional background color
 * @return string
 */
function generate_textarea($label, $content, $average_chars_per_row, $background = null)
{
    $rows = ceil(strlen($content) / $average_chars_per_row);
    $style = $background ? "style='background-color: $background;'" : "";

    return "<p>
                <label class='large-font'>$label</label><br>
                <textarea class='output' rows='{$rows}' cols='40' $style>$content</textarea>
            </p>";
}
function get_textarea_style()
{
    return "<style>
                .textarea-no-interaction {
                    pointer-events: none;
                    user-select: none;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                }
            </style>";
}

?>