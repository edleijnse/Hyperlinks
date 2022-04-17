<?php
require_once("HyperlinksRestHandler.php");
// echo "let's dance!";

$view = "single";
if (isset($_GET["view"]))
    $view = $_GET["view"];
// echo "view: " + $view;
/*
controls the RESTful services
URL mapping
*/

switch ($view) {

    case "all":
        // to handle REST Url /mobile/list/
        $hyperlinksRestHandler = new HyperLinksRestHandler();
        $hyperlinksRestHandler->getAllHyperlinks();
        break;

    case "single":
        // to handle REST Url /mobile/show/<id>/
        $hyperlinksRestHandler = new HyperLinksRestHandler();
        $hyperlinksRestHandler->getHyperlink($_GET["ID"]);
        break;

    case "" :
        //404 - not found;
        break;
}

// echo "that's all folks!"
?>
