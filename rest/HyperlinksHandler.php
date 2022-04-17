<?php

/*
A domain Class to demonstrate RESTful web services
*/

class Hyperlink
{
    public $ID;
    public $group;
    public $category;
    public $webdescription;
    public $website;
}

class HyperlinksHandler
{


    private $hyperlinks = array();

    /*
        you should hookup the DAO here
    */
    public function getAllHyperlinks()
    {
        $hyperLink1 = new Hyperlink();
        $hyperLink1->ID = 1;
        $hyperLink1->category = "Museums";
        $hyperLink1->webdescription = "Artists Keith Haring";
        $hyperLink1->website = "https://haring.com";
        $hyperlinks[0] = $hyperLink1;
        $hyperLink2 = new Hyperlink();
        $hyperLink2->ID = 2;
        $hyperLink2->category = "Museums";
        $hyperLink2->webdescription = "Artists Hans Glanzmann";
        $hyperLink1->website = "https://glanzmann.info";
        $hyperlinks[1] = $hyperLink2;
        return $hyperlinks;
    }

    public function getHyperlink($id)
    {
        $hyperLink1 = new Hyperlink();
        $hyperLink1->ID = 1;
        $hyperLink1->category = "Museums";
        $hyperLink1->webdescription = "Artists Keith Haring";
        $hyperLink1->website = "https://haring.com";
        $hyperlinks[0] = $hyperLink1;
        return $hyperlinks;
    }
    public function insertHyperlink($ID, $group, $category, $webdescription, $website)
    {
        return true;
    }
}

?>