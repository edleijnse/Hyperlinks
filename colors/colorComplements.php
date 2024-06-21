<?php
// Define arc positions
$arcPositions = [
    "red" => 200,
    "green" => 200,
    "blue" => 200,
    "yellow" => 200,
    "reset" => 200
];
$createColor = "";

// Check if there are any query parameters
if ($_SERVER['QUERY_STRING']) {
    parse_str($_SERVER['QUERY_STRING'], $params);

    if (isset($params['red'])) {
        $arcPositions['red'] = $params['red'];
        $createColor = 'red';
    }
    if (isset($params['green'])) {
        $arcPositions['green'] = $params['green'];
        $createColor = 'green';
    }
    if (isset($params['blue'])) {
        $arcPositions['blue'] = $params['blue'];
        $createColor = 'blue';
    }
    if (isset($params['yellow'])) {
        $arcPositions['yellow'] = $params['yellow'];
        $createColor = 'yellow';
    }
    if (isset($params['reset'])) {
        $arcPositions['reset'] = $params['reset'];
        $createColor = 'reset';
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
/**
 * @param GdImage|bool $image
 * @param bool|int $white
 * @param bool|int $xRedBright
 * @param bool|int $xRedDark
 * @param bool|int $xVioletDark
 * @param bool|int $xBlue
 * @param bool|int $xBlueDark
 * @param bool|int $xBlueMedium
 * @param bool|int $xBlueLight
 * @param bool|int $xGreenDark
 * @param bool|int $xGreenLight
 * @param bool|int $xyellowBright
 * @param bool|int $xOrangeBright
 * @param bool|int $xOrangeDark
 * @return void
 */
function johannesItenFarbkreis(GdImage|bool $image, bool|int $white, bool|int $xRedBright, bool|int $xRedDark, bool|int $xVioletDark, bool|int $xBlue, bool|int $xBlueDark, bool|int $xBlueMedium, bool|int $xBlueLight, bool|int $xGreenDark, bool|int $xGreenLight, bool|int $xyellowBright, bool|int $xOrangeBright, bool|int $xOrangeDark): void
{
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
}

johannesItenFarbkreis($image, $white, $xRedBright, $xRedDark, $xVioletDark, $xBlue, $xBlueDark, $xBlueMedium, $xBlueLight, $xGreenDark, $xGreenLight, $xyellowBright, $xOrangeBright, $xOrangeDark);

if ($createColor == 'red') {
    johannesItenFarbkreis($image, $white, $white, $xRedDark, $white, $white, $white, $white, $white, $xGreenDark, $white, $white, $white, $white);
}
if ($createColor == 'green') {
    johannesItenFarbkreis($image, $white, $white, $xRedDark, $white, $white, $white, $white, $white, $xGreenDark, $white, $white, $white, $white);
}
if ($createColor == 'blue') {
    johannesItenFarbkreis($image, $white, $white, $white, $white, $xBlue, $white, $white, $white, $white, $white, $xyellowBright, $white, $white);
}
if ($createColor == 'yellow') {
    johannesItenFarbkreis($image, $white, $white, $white, $white, $xBlue, $white, $white, $white, $white, $white, $xyellowBright, $white, $white);
}
if ($createColor == 'reset') {
    johannesItenFarbkreis($image, $white, $xRedBright, $xRedDark, $xVioletDark, $xBlue, $xBlueDark, $xBlueMedium, $xBlueLight, $xGreenDark, $xGreenLight, $xyellowBright, $xOrangeBright, $xOrangeDark);
}
// Output the image to browser
header('Content-Type: image/png');
imagepng($image);

// Free up memory
imagedestroy($image);
?>
