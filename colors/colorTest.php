<?php
// Create a blank image
$image = imagecreatetruecolor(400, 400);

// Allocate colors
$white = imagecolorallocate($image, 255, 255, 255);
$red = imagecolorallocate($image, 255, 0, 0);
$green = imagecolorallocate($image, 0, 255, 0);
$blue = imagecolorallocate($image, 0, 0, 255);
$yellow = imagecolorallocate($image, 255, 255, 0);
$violet = imagecolorallocate($image, 238, 130, 238);
$black = imagecolorallocate($image, 0, 0, 0);
$orange = imagecolorallocate($image, 255, 165, 0);
$cyan = imagecolorallocate($image, 0, 255, 255);
$magenta = imagecolorallocate($image, 255, 0, 255);


imagefill($image, 0, 0, $white);

imagefilledarc($image, 200, 200, 300, 300, 0, 36, $white, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 36, 72, $red, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 72, 108, $green, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 108, 144, $blue, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 144, 180, $yellow, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 180, 216, $violet, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 216, 252, $black, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 252, 288, $orange, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 288, 324, $cyan, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 324, 360, $magenta, IMG_ARC_PIE);


// Output the image to browser
header('Content-Type: image/png');
imagepng($image);

// Free up memory
imagedestroy($image);
?>
