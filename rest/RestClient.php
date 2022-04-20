<?php
$url = 'https://leijnse.info/hyperlinks/rest/Restcontroller.php/?command=all&count=900&from=0';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response_json = curl_exec($ch);
curl_close($ch);
$hyperlinks = array();
$hyperlinks = json_decode($response_json, true);
foreach($hyperlinks as $hyperlink){
    print($hyperlink['ID']) . ", " . $hyperlink['group'] . ", " . $hyperlink['category']
        . ", " . $hyperlink['webdescription']
        . ", " . $hyperlink['website']
        . PHP_EOL;
}

