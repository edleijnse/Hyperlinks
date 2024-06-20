<?php
// Define arc positions
$arcPositions = [
    "red" => 200,
    "green" => 200,
    "blue" => 200
];

// Check if there are any query parameters
if ($_SERVER['QUERY_STRING']) {
    parse_str($_SERVER['QUERY_STRING'], $params);

    if (isset($params['red'])) {
        $arcPositions['red'] = $params['red'];
    }
    if (isset($params['green'])) {
        $arcPositions['green'] = $params['green'];
    }
    if (isset($params['blue'])) {
        $arcPositions['blue'] = $params['blue'];
    }
}
// Create a blank image
$image = imagecreatetruecolor(400, 400);

// Allocate colors
$white = imagecolorallocate($image, 255, 255, 255);

$xyellowBright = imagecolorallocate($image, 248, 204, 4);
$xOrangeBright = imagecolorallocate($image, 255, 188, 0);
$xOrangeDark = imagecolorallocate($image, 255, 147, 0);
$xRedBright = imagecolorallocate($image, 255, 91, 0);
$xRedDark = imagecolorallocate($image, 255, 0, 0);
$xVioletDark = imagecolorallocate($image, 89, 17, 68);
$xBlue = imagecolorallocate($image, 0, 0, 255);
$xBlueDark = imagecolorallocate($image, 0, 93, 255);
$xBlueMedium = imagecolorallocate($image, 0, 134, 255);
$xBlueLight = imagecolorallocate($image, 0, 180, 255);
$xGreenLight = imagecolorallocate($image, 72, 190, 57);
$xGreenDark = imagecolorallocate($image, 55, 163, 41);

$red = imagecolorallocate($image, 255, 0, 0);
$green = imagecolorallocate($image, 0, 255, 0);
$blue = imagecolorallocate($image, 0, 0, 255);
$yellow = imagecolorallocate($image, 255, 255, 0);
$violet = imagecolorallocate($image, 238, 130, 238);
$black = imagecolorallocate($image, 0, 0, 0);
$orange = imagecolorallocate($image, 255, 165, 0);
$cyan = imagecolorallocate($image, 0, 255, 255);
$magenta = imagecolorallocate($image, 255, 0, 255);

// Johannes Iten Farbkreis
imagefill($image, 0, 0, $white);

imagefilledarc($image, 200, 200, 300, 300, 0, 30, $xRedBright, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 30, 60, $xRedDark, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 60, 90, $xVioletDark, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 90, 120, $xBlue, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 120, 150, $xBlueDark, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 150, 180, $xBlueMedium, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 180, 210, $xBlueLight, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 210, 240, $xGreenDark, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 240, 270, $xGreenLight, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 270, 300, $xyellowBright, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 300, 330, $xOrangeBright, IMG_ARC_PIE);

imagefilledarc($image, 200, 200, 300, 300, 330, 360, $xOrangeDark, IMG_ARC_PIE);
//
imagefilledarc($image, $arcPositions['red'], 200, 300, 300, 0, 30, $xRedBright, IMG_ARC_PIE);

// Output the image to browser
header('Content-Type: image/png');
imagepng($image);

// Free up memory
imagedestroy($image);
?>
