<?php
require_once("SimpleRest.php");
require_once("HyperlinksHandler.php");

class HyperLinksRestHandler extends SimpleRest
{

    function getAllHyperlinks()
    {

        $hyperlinksHandler = new HyperlinksHandler();
        $rawData = $hyperlinksHandler->getAllHyperlinks();

        if (empty($rawData)) {
            $statusCode = 404;
            $rawData = array('error' => 'No mobiles found!');
        } else {
            $statusCode = 200;
        }
        // $response = $this->encodeJson($rawData);

        // echo $response;

        // $requestContentType = $_SERVER['HTTP_ACCEPT'];
        $requestContentType = "application/json";
        $this->setHttpHeaders($requestContentType, $statusCode);

        if (strpos($requestContentType, 'application/json') !== false) {
            $response = $this->encodeJson($rawData);
            echo $response;
        } else if (strpos($requestContentType, 'text/html') !== false) {
            $response = $this->encodeHtml($rawData);
            echo $response;
        } else if (strpos($requestContentType, 'application/xml') !== false) {
            $response = $this->encodeXml($rawData);
            echo $response;
        }
    }

    public function encodeHtml($responseData)
    {

        $htmlResponse = "<table border='1'>";
        foreach ($responseData as $key => $value) {
            $htmlResponse .= "<tr><td>" . $key . "</td><td>" . $value . "</td></tr>";
        }
        $htmlResponse .= "</table>";
        return $htmlResponse;
    }

    public function encodeJson($responseData)
    {
        $jsonResponse = json_encode($responseData);
        return $jsonResponse;
    }

    public function encodeXml($responseData)
    {
        // creating object of SimpleXMLElement
        $xml = new SimpleXMLElement('<?xml version="1.0"?><mobile></mobile>');
        foreach ($responseData as $key => $value) {
            $xml->addChild($key, $value);
        }
        return $xml->asXML();
    }

    public function getHyperlink($id)
    {

        $hyperlinksHandler = new HyperlinksHandler();
        $rawData = $hyperlinksHandler->getHyperlink($id);

        if (empty($rawData)) {
            $statusCode = 404;
            $rawData = array('error' => 'No mobiles found!');
        } else {
            $statusCode = 200;
        }
        // $response = $this->encodeJson($rawData);
        // echo $response;

        $requestContentType = "application/json";
        $this->setHttpHeaders($requestContentType, $statusCode);

        if (strpos($requestContentType, 'application/json') !== false) {
            $response = $this->encodeJson($rawData);
            $response = str_replace("[","",$response);
            $response = str_replace("]","",$response);

            echo $response;
        } else if (strpos($requestContentType, 'text/html') !== false) {
            $response = $this->encodeHtml($rawData);
            echo $response;
        } else if (strpos($requestContentType, 'application/xml') !== false) {
            $response = $this->encodeXml($rawData);
            echo $response;
        }
    }
}

?>