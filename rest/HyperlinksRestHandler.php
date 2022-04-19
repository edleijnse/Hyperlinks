<?php
require_once("SimpleRest.php");
require_once("HyperlinksHandler.php");

class HyperLinksRestHandler extends SimpleRest
{

    function getAllHyperlinks($myCount,$myFrom, $mySearch)
    {

        $hyperlinksHandler = new HyperlinksHandler();
        $rawData = $hyperlinksHandler->getAllHyperlinks($myCount,$myFrom, $mySearch);

        if (empty($rawData)) {
            $statusCode = 404;
            $rawData = array('error' => 'No data found!');
        } else {
            $statusCode = 200;
        }
        // $response = $this->encodeJson($rawData);

        // echo $response;

        // $requestContentType = $_SERVER['HTTP_ACCEPT'];
        $requestContentType = "application/json";
        //$this->setHttpHeaders($requestContentType, $statusCode);

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

        $htmlResponse = "<table >";
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
    public function insertHyperlink($ID, $group, $category, $webdescription, $website)
    {
        $hyperlinksHandler = new HyperlinksHandler();
        $returncode = $hyperlinksHandler->insertHyperlink($ID, $group, $category, $webdescription, $website);
        $requestContentType = "application/json";
        $statusCode = 200;
        $this->setHttpHeaders($requestContentType, $statusCode);

        if (strpos($requestContentType, 'application/json') !== false) {
            $response = $this->encodeJson("OK");
            echo $response;
        } else if (strpos($requestContentType, 'text/html') !== false) {
            $response = $this->encodeHtml("OK");
            echo $response;
        } else if (strpos($requestContentType, 'application/xml') !== false) {
            $response = $this->encodeXml("OK");
            echo $response;
        }
    }
    public function deleteHyperlink($ID)
    {
        $hyperlinksHandler = new HyperlinksHandler();
        $returncode = $hyperlinksHandler->deleteHyperlink($ID);
        $requestContentType = "application/json";
        $statusCode = 200;
        $this->setHttpHeaders($requestContentType, $statusCode);

        if (strpos($requestContentType, 'application/json') !== false) {
            $response = $this->encodeJson("OK");
            echo $response;
        } else if (strpos($requestContentType, 'text/html') !== false) {
            $response = $this->encodeHtml("OK");
            echo $response;
        } else if (strpos($requestContentType, 'application/xml') !== false) {
            $response = $this->encodeXml("OK");
            echo $response;
        }
    }
    public function getHyperlink($id)
    {

        $hyperlinksHandler = new HyperlinksHandler();
        $rawData = $hyperlinksHandler->getHyperlink($id);

        if (empty($rawData)) {
            $statusCode = 404;
            $rawData = array('error' => 'No data found!');
        } else {
            $statusCode = 200;
        }
        // $response = $this->encodeJson($rawData);
        // echo $response;

        $requestContentType = "application/json";
        $this->setHttpHeaders($requestContentType, $statusCode);

        if (strpos($requestContentType, 'application/json') !== false) {
            $response = $this->encodeJson($rawData);
            // $response = str_replace("[","",$response);
            // $response = str_replace("]","",$response);

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