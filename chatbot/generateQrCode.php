<!DOCTYPE html>
<html>
<?php include 'head.php'; ?>
<body>
<h3 class="ask">Generate QR Code</h3>
<p>
<form method="post">
    <label class="ask" for="input_text">Enter QR Code text:</label>
    <br>
    <textarea name="input_text" class="input" rows="5" cols="50">
        <?php if (isset($_POST['input_text'])) { echo htmlentities($_POST['input_text']); } else { echo ""; } ?>
    </textarea>
    <br>
    <br>

    <input type="submit" name="submit_button" class="ask" value="Generate QR Code">
</form>
</p>
<?php
require 'vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;


if (isset($_POST['submit_button'])) {
    if (empty($_POST['input_text'])) {
        echo '<p class="error-message">Enter QRCode text</p>';
    } else {
        // Load the Guzzle library
        // Get the input text
        $input_text = $_POST['input_text'];

        // Create a QR code instance

        $options = new QROptions(
            [
                'eccLevel' => QRCode::ECC_L,
                'outputType' => QRCode::OUTPUT_MARKUP_SVG,
                'version' => 5,
            ]
        );

        // Instantiating the code QR code class

// Set the data for the QR code
        $data = $input_text;
        $qrcode = (new QRCode($options))->render($data);

    }
}
?>
<div class="container">
    <img src='<?= $qrcode ?>' alt='QR Code' width='200' height='200'>
</div>
</body>
</html>
