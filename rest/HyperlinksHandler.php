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
        $cfg_dsn = "DRIVER=Microsoft Access Driver (*.mdb, *.accdb);
DBQ=D:/www/www780/database/hyperlinks.mdb;
UserCommitSync=Yes;
Threads=3;
SafeTransactions=0;
PageTimeout=5;
MaxScanRows=8;
MaxBufferSize=2048;
DriverId=281;
DefaultDir=D:/www/www780/database";
        $adodb_path = "d:/www/www780/adodb";
        include("$adodb_path/adodb.inc.php"); // includes the adodb library
        include("$adodb_path/drivers/adodb-odbc.inc.php"); // includes the odbc driver
        $Quelle = odbc_connect($cfg_dsn, "", "");
        $MyCmdStr = "INSERT INTO hyperlinks  VALUES (";
        $MyCmdStr = $MyCmdStr . $ID . ",";
        $MyCmdStr = $MyCmdStr . "'". $group . "'".",";
        $MyCmdStr = $MyCmdStr . "'" . $category . "'". ",";
        $MyCmdStr = $MyCmdStr . "'" . $webdescription ."'" . ",";
        $MyCmdStr = $MyCmdStr . "'" . $website . "'" ;
        $MyCmdStr = $MyCmdStr . ');';
        $Result = odbc_exec($Quelle, $MyCmdStr);
        return $Result;
    }
}

?>