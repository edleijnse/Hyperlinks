<?php
require_once("HyperlinksRestHandler.php");
// echo "let's dance!";

$command = "all";
$myId = 1;
$myGroup = "";
$myCategory = "";
$myWebdescription = "";
$myWebsite = "";
$myCount = 100;
$myFrom = 0;
$mySearch = "";
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
if (isset($_GET["search"]))
    $mySearch = $_GET["search"];
// echo "view: " + $view;
/*
controls the RESTful services
URL mapping
*/

switch ($command) {

    case "all":
        // to handle REST Url /mobile/list/
        $hyperlinksRestHandler = new HyperLinksRestHandler();
        $hyperlinksRestHandler->getAllHyperlinks($myCount, $myFrom, $mySearch);
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
    case "delete":
        // to handle REST Url /mobile/show/<id>/
        $hyperlinksRestHandler = new HyperLinksRestHandler();
        $hyperlinksRestHandler->deleteHyperlink($myId);
        break;
    case "allmysql":
        // to handle REST Url /mobile/list/
        $hyperlinksRestHandler = new HyperLinksRestHandler();
        $hyperlinksRestHandler->getAllHyperlinksMySql($myCount, $myFrom, $mySearch);
        break;

    case "singlemysql":
        // to handle REST Url /mobile/show/<id>/
        $hyperlinksRestHandler = new HyperLinksRestHandler();
        $hyperlinksRestHandler->getHyperlinkMySql($myId);
        break;
    case "insertmysql":
        // to handle REST Url /mobile/show/<id>/
        $hyperlinksRestHandler = new HyperLinksRestHandler();
        $hyperlinksRestHandler->insertHyperlinkMySql($myId,$myGroup, $myCategory, $myWebdescription, $myWebsite);
        break;
    case "updatemysql":
        // to handle REST Url /mobile/show/<id>/
        $hyperlinksRestHandler = new HyperLinksRestHandler();
        $hyperlinksRestHandler->updateHyperlinkMySql($myId,$myGroup, $myCategory, $myWebdescription, $myWebsite);
        break;
    case "deletemysql":
        // to handle REST Url /mobile/show/<id>/
        $hyperlinksRestHandler = new HyperLinksRestHandler();
        $hyperlinksRestHandler->deleteHyperlinkMySql($myId);
        break;

    case "" :
        //404 - not found;
        break;
}

// echo "that's all folks!"
?>
