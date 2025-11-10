# Hyperlinks
PHP Database and Restful webservices 
A sample for Restful Webservices with a MySql Database written in PHP.

Added in December 2022: 
a sample of a Chatbot Client to communicate with the
OpenAI API.(chatbotclientGuzzle.php).
Preparation: in your terminal:
composer require guzzlehttp/guzzle
Note: You have to add your own "api_key" file into the parent directory.
Added in January 2023:
Question stored after submitting
Multilingual support for English, German and Dutch

openai_functions.php to have common functions for communicating with openai

September 2024
ChatHistory in chatbotclientGuzzle.php
openai_functions.php refactored with Jetbrains AI assistant

### Chatbot Client (chatbot/chatbotclientGuzzle.php)

This is a browser-based demo client that talks to the OpenAI API using Guzzle. It renders an HTML form with:
- a large text area for your prompt
- optional image upload (multimodal)
- model selection (radio buttons): `gpt-5-mini` or `gpt-5`
- buttons to submit, clean the text input, and clear the chat history

It keeps a running conversation in the current PHP session (`$_SESSION['content_history']`).

#### Prerequisites
- PHP 8.x with sessions enabled
- Composer
- Guzzle HTTP client dependency (already noted below)
- An OpenAI API key stored in a file named `api_key` placed in the parent directory of `chatbot` (as already noted in this repo)

Install vendor dependencies (from the `chatbot` directory):
```
composer require guzzlehttp/guzzle
```

#### Quick start
Option A — PHP built-in web server:
1. Open a terminal in the project root (or directly in the `chatbot` directory).
2. Ensure `chatbot/vendor/autoload.php`, `chatbot/openai_functions.php`, and `chatbot/head.php` exist.
3. Ensure your API key file `api_key` is located one level above `chatbot`.
4. Start a local server from inside `chatbot`:
   ```
   php -S localhost:8000 -t .
   ```
5. Open your browser at:
   ```
   http://localhost:8000/chatbotclientGuzzle.php
   ```

Option B — Docker (optional):
- A sample `dockerlocalwebserver` is included in this repository. Use it if you prefer a containerized runtime. Ensure the `api_key` file is mounted/available to the PHP runtime the same way (one level above the app path) or adjust `openai_functions.php` accordingly.

#### How it works
- Session-backed chat history: The page initializes `$_SESSION['content_history']` and appends each turn: a `QUESTION:` line and the model `ANSWER:`.
- Model selection: The chosen value is stored in the session as `model_choice` and used when calling OpenAI.
- Image input (optional): If you attach a PNG/JPEG/GIF/WEBP, the file is validated and base64-encoded as a `data:` URL and passed to the OpenAI function for multimodal prompts.
- Response generation: The heavy lifting is delegated to `openai_functions.php`, via `get_openai_response_for_model($input_text, $selected_model, $client, $content_history, $image_data_url)`.
- Upload safety and limits: The script proactively checks `post_max_size` and `upload_max_filesize` and surfaces friendly messages when your request is too large or the upload fails.

#### Main functions in `chatbotclientGuzzle.php`
- `_to_bytes($val)`: Utility to convert shorthand sizes (e.g., `50M`) into bytes.
- `handleFormSubmission()`: Central handler for all form buttons (submit, clean input, clear history) and persisting selected model.
- `displayModelChoices()`: Renders radio buttons for `gpt-5-mini` and `gpt-5` and ensures the current selection is stored.
- `generateRadioOption($id, $label, $selected_model)`: Small helper to output one radio option.
- `initializeContentHistory()`: Ensures the session history array exists.
- `getDisplayText()`: Returns the current input text back into the textarea after submit.
- `processUserInput($input_text)`: Prepares the multimodal prompt (optionally with image), calls OpenAI, and appends question/answer to the session history.
- `cleanInputText()`: Clears the current textarea input.
- `clearContentHistory()`: Clears the entire conversation history for the session.

#### Using the page
1. Pick a model (default is `gpt-5-mini`).
2. Type your question in the large textbox.
3. Optionally upload a small image (PNG, JPEG, GIF, WEBP) if your prompt references it.
4. Click Submit. A brief "answering" overlay appears; then the reply is appended below.
5. Use Clean to empty the textbox; use Clear History to wipe the session conversation.

#### Configuration and limits
- API key: Keep `api_key` in the parent directory of `chatbot` or adapt `openai_functions.php` to your environment.
- PHP limits: Very large requests may be blocked by `post_max_size` or `upload_max_filesize`. Increase them in your `php.ini` if needed (e.g., `post_max_size = 32M`, `upload_max_filesize = 16M`).
- Allowed image MIME types: `image/png`, `image/jpeg`, `image/gif`, `image/webp`.

#### Troubleshooting
- "The request is too large": Your form data exceeded `post_max_size`. Reduce the image size or raise the limit.
- "The selected image exceeds the upload limit": The file is bigger than `upload_max_filesize`.
- Other upload errors (partial upload, cannot write temp file, etc.) are reported with human-friendly messages by the script.
- Empty replies: Check that your API key is valid and that `openai_functions.php` is accessible. Review PHP error logs for details.

#### Files involved
- `chatbot/chatbotclientGuzzle.php` — the UI/controller described above
- `chatbot/openai_functions.php` — shared OpenAI integration helpers
- `chatbot/head.php` — shared stylesheet and meta includes used by the page
- `chatbot/vendor/autoload.php` — Composer autoloader (ensure Guzzle is installed)

Last updated: 2025-11-10
