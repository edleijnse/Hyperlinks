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
    // Allow optional environment toggles for troubleshooting TLS/HTTP2 issues
    $forceHttp11 = getenv('OPENAI_FORCE_HTTP1_1') ?: '1'; // default on
    $disableCompression = getenv('OPENAI_DISABLE_COMPRESSION') ?: '1'; // default on
    $verifyCA = getenv('OPENAI_SSL_VERIFY'); // null means use default (true)

    $headers = [
        'Authorization' => "Bearer $api_key",
        'Content-Type' => 'application/json',
    ];
    if ($disableCompression === '1' || strtolower($disableCompression) === 'true') {
        // Some middleboxes and certain curl/OpenSSL combos mis-handle compressed responses
        $headers['Accept-Encoding'] = 'identity';
    }
    // Avoid "Expect: 100-continue" which sometimes trips up proxies/middleboxes
    $headers['Expect'] = '';

    $config = [
        'base_uri' => 'https://api.openai.com',
        'headers' => $headers,
        // Conservative timeouts
        'timeout' => 60,
        'connect_timeout' => 15,
        // Ensure errors throw exceptions so we can retry selectively
        'http_errors' => true,
    ];

    // Force HTTP/1.1 if requested (helps with certain HTTP/2/TLS stacks)
    if ($forceHttp11 === '1' || strtolower($forceHttp11) === 'true') {
        $config['curl'] = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ];
        // Also set default request version for non-curl handlers
        $config['version'] = 1.1;
    }

    if ($verifyCA !== false && $verifyCA !== null && $verifyCA !== '') {
        // If a specific CA bundle path is provided via env, use it
        $config['verify'] = $verifyCA;
    } elseif ($verifyCA === '0' || strtolower((string)$verifyCA) === 'false') {
        // Not recommended, but can be toggled for diagnostics
        $config['verify'] = false;
    }

    return new Client($config);
}

/**
 * Get a response from the OpenAI API using the specified model.
 *
 * @param string $input_text
 * @param string $model
 * @param array $content_history
 * @param Client $client
 * @param string|null $image_data_url Optional data URL for an image to include with the user's message
 * @return string|null
 */
function get_openai_response_for_model($input_text, $model, Client $client, array $content_history = [], $image_data_url = null)
{
    $messages = prepare_messages($input_text, $content_history, $image_data_url);
    $requestBody = ['model' => $model, 'messages' => $messages];
    $response = make_request($client, $requestBody);
    $completion = $response['choices'][0]['message']['content'] ?? null;

    // Commented out to prevent HTML output before DOCTYPE
    // display_interaction($input_text, $completion, $content_history);

    return $completion;
}

/**
 * Prepare message history by appending the current input.
 *
 * @param string $input_text
 * @param array $content_history
 * @return array
 */
/**
 * Prepare message history by appending the current input (and optional image).
 *
 * @param string $input_text
 * @param array $content_history
 * @param string|null $image_data_url data URL like "data:image/png;base64,..."
 * @return array
 */
function prepare_messages($input_text, array $content_history, $image_data_url = null)
{
    $messages = [];
    
    // Add a system prompt to ensure the AI stays in the same language as the user
    $messages[] = [
        'role' => 'system',
        'content' => 'You are a helpful assistant. Always respond in the same language as the user\'s last message. If the user asks in French, respond in French. If they ask in English, respond in English.'
    ];

    foreach ($content_history as $entry) {
        if (strpos($entry, 'QUESTION: ') === 0) {
            $messages[] = ['role' => 'user', 'content' => substr($entry, 10)];
        } elseif (strpos($entry, 'ANSWER: ') === 0) {
            $messages[] = ['role' => 'assistant', 'content' => substr($entry, 8)];
        } else {
            // Fallback for any entries without prefix
            $messages[] = ['role' => 'user', 'content' => $entry];
        }
    }

    // Build the current user message, optionally as a multimodal (text + image) content array
    if ($image_data_url) {
        $current_content = [
            ['type' => 'text', 'text' => $input_text],
            ['type' => 'image_url', 'image_url' => ['url' => $image_data_url]],
        ];
    } else {
        $current_content = $input_text;
    }

    $messages[] = ['role' => 'user', 'content' => $current_content];

    return $messages;
}

/**
 * Generate an image with the current OpenAI Image API and persist it locally.
 *
 * @param string $prompt
 * @param Client $client
 * @param string $model
 * @return string|null A browser-accessible URL for the generated image
 */
function generate_openai_image($prompt, Client $client, $model = 'gpt-image-2')
{
    $prompt = trim((string)$prompt);
    if ($prompt === '') {
        error_log('OpenAI image generation skipped: the prompt is empty.');
        return null;
    }

    $requestBody = [
        'model' => $model,
        'prompt' => $prompt,
        'n' => 1,
        'size' => '1024x1024',
        'output_format' => 'png',
    ];

    try {
        $response = $client->post('/v1/images/generations', [
            'json' => $requestBody,
            // Image generation can legitimately take longer than chat requests.
            'timeout' => 180,
        ]);
        $responseBody = json_decode(
            $response->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $image = $responseBody['data'][0] ?? null;
        if (!is_array($image)) {
            throw new RuntimeException('The API response contains no image data.');
        }

        $base64Image = $image['b64_json'] ?? null;
        if (!is_string($base64Image) || $base64Image === '') {
            throw new RuntimeException('The API response contains no base64 image data.');
        }

        $imageBytes = base64_decode($base64Image, true);
        if ($imageBytes === false) {
            throw new RuntimeException('The API returned invalid base64 image data.');
        }

        if ($imageBytes === '') {
            throw new RuntimeException('The generated image is empty.');
        }
        if (strlen($imageBytes) > 25 * 1024 * 1024) {
            throw new RuntimeException('The generated image exceeds the 25 MB storage limit.');
        }

        $imageInfo = @getimagesizefromstring($imageBytes);
        $extensions = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
        ];
        $extension = is_array($imageInfo) ? ($extensions[$imageInfo[2]] ?? null) : null;
        if ($extension === null) {
            throw new RuntimeException('The API response is not a supported image format.');
        }

        $directory = __DIR__ . DIRECTORY_SEPARATOR . 'generated_images';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create the generated image directory.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        if (file_put_contents($path, $imageBytes, LOCK_EX) === false) {
            throw new RuntimeException('Unable to save the generated image.');
        }

        $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $scriptDirectory = $scriptDirectory === '/' || $scriptDirectory === '.'
            ? ''
            : rtrim($scriptDirectory, '/');

        return $scriptDirectory . '/generated_images/' . rawurlencode($filename);
    } catch (Throwable $e) {
        error_log('OpenAI image generation failed: ' . $e->getMessage());
        return null;
    }
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
