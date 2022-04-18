<?php
require_once("HyperlinksRestHandler.php");
// echo "let's dance!";

$command = "all";
$myId = 1;
$myGroup = "Windööws";
$myCategory = "Tüüls";
$myWebdescription = "Performance";
$myWebsite = "https://leijnse.info";
$myCount = 25;
$myFrom = 111;
if (isset($_GET["command"]))
    $command = $_GET["command"];
if (isset($_GET["ID"]))
    $myId = $_GET["ID"];
if (isset($_GET["group"]))
    $myGroup = $_GET["group"];
if (isset($_GET["category"]))
    $myCategory = $_GET["category"];
if (isset($_GET["webdescription"]))
    $myWebdescription = $_GET["webdescription"];
if (isset($_GET["website"]))
    $myWebsite = $_GET["website"];
if (isset($_GET["count"]))
    $myCount = $_GET["count"];
if (isset($_GET["from"]))
    $myFrom = $_GET["from"];
// echo "view: " + $view;
/*
controls the RESTful services
URL mapping
*/

switch ($command) {

    case "all":
        // to handle REST Url /mobile/list/
        $hyperlinksRestHandler = new HyperLinksRestHandler();
        $hyperlinksRestHandler->getAllHyperlinks($myCount, $myFrom);
        break;

    case "single":
        // to handle REST Url /mobile/show/<id>/
        $hyperlinksRestHandler = new HyperLinksRestHandler();
        $hyperlinksRestHandler->getHyperlink($myId);
        break;
    case "insert":
        // to handle REST Url /mobile/show/<id>/
        $hyperlinksRestHandler = new HyperLinksRestHandler();
        $hyperlinksRestHandler->insertHyperlink($myId,$myGroup, $myCategory, $myWebdescription, $myWebsite);
        break;

    case "" :
        //404 - not found;
        break;
}

// echo "that's all folks!"
?>
