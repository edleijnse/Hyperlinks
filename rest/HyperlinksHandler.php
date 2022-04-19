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
    var $cfg_dsn = "DRIVER=Microsoft Access Driver (*.mdb, *.accdb);
DBQ=D:/www/www780/database/hyperlinks.mdb;
UserCommitSync=Yes;
Threads=3;
SafeTransactions=0;
PageTimeout=5;
MaxScanRows=8;
MaxBufferSize=2048;
DriverId=281;
DefaultDir=D:/www/www780/database";
    var $adodb_path = "d:/www/www780/adodb";

    /*
        you should hookup the DAO here
    */
    public function getAllHyperlinks($myCount, $myFrom)
    {
        $myadodbpath = $this->adodb_path;
        $mycfg_dsn = $this->cfg_dsn;
        include($myadodbpath . "/adodb.inc.php"); // includes the adodb library
        include($myadodbpath . "/drivers/adodb-odbc.inc.php"); // includes the odbc driver
        $database_type = "access";
        $host = $mycfg_dsn;
        $user = "";
        $password = "";
        $database_name = "hyperlinks.mdb";

        $hyperlinks = array();
        $db = NewADOConnection("$database_type"); // A new connection
        $db->Connect("$host", "$user", "$password", "$database_name");
        // echo "CONNECTED";
        $sql = "SELECT * from hyperlinks order by group, category, webdescription, website";
        $rs = $db->Execute($sql);
        if (!$rs) {
            print $db->ErrorMsg(); // Displays the error message if no results could be returned
            // echo ErrorMsg();
        } else {
            // echo "execute OK";
            $iiCount=0;
            $iiFrom=0;
            while (!$rs->EOF) {
                // echo "record found";
                if ($iiFrom >= $myFrom){
                    if ($iiCount < $myCount) {
                        // print iconv("ISO-8859-1", "UTF-8", $rs->fields[1]) . ' ' . $rs->fields[1] . ' ' . $rs->fields[2] . ' ' . $rs->fields[3] . '<BR>';
                        $hyperLink1 = new HyperLink();
                        $hyperLink1->ID = $rs->fields[0];
                        $mygroup=$rs->fields[1];
                        $mygroupconv=iconv("ISO-8859-1", "UTF-8", $mygroup);
                        $hyperLink1->group=$mygroupconv;
                        $mycategory=$rs->fields[2];
                        $mycategoryconv=iconv("ISO-8859-1", "UTF-8", $mycategory);
                        $hyperLink1->category=$mycategoryconv;
                        $mywebsitedescription=$rs->fields[3];
                        $mywebsitedescriptionconv=iconv("ISO-8859-1", "UTF-8", $mywebsitedescription);
                        $hyperLink1->webdescription=$mywebsitedescriptionconv;
                        $mywebsite=$rs->fields[4];
                        $mywebsiteconv=iconv("ISO-8859-1", "UTF-8", $mywebsite);
                        $hyperLink1->website=$mywebsiteconv;
                        $hyperlinks[$iiCount] = $hyperLink1;
                    }
                    $iiCount++;
                }
                $iiFrom++;
                $rs->MoveNext();  //  Moves to the next row
            }  // end while
        } // end else

        return $hyperlinks;
    }

    public function getHyperlink($id)
    {
        $myadodbpath = $this->adodb_path;
        $mycfg_dsn = $this->cfg_dsn;
        include($myadodbpath . "/adodb.inc.php"); // includes the adodb library
        include($myadodbpath . "/drivers/adodb-odbc.inc.php"); // includes the odbc driver
        $Quelle = odbc_connect($mycfg_dsn, "", "");
        $hyperlinks = array();
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
        $myadodbpath = $this->adodb_path;
        $mycfg_dsn = $this->cfg_dsn;
        include($myadodbpath . "/adodb.inc.php"); // includes the adodb library
        include($myadodbpath . "/drivers/adodb-odbc.inc.php"); // includes the odbc driver
        $Quelle = odbc_connect($mycfg_dsn, "", "");
        $MyCmdStr = "INSERT INTO hyperlinks  VALUES (";
        $MyCmdStr = $MyCmdStr . $ID . ",";
        $MyCmdStr = $MyCmdStr . "'". iconv("UTF-8","ISO-8859-1//TRANSLIT",$group) . "'".",";
        $MyCmdStr = $MyCmdStr . "'". iconv("UTF-8","ISO-8859-1//TRANSLIT",$category) . "'".",";
        $MyCmdStr = $MyCmdStr . "'". iconv("UTF-8","ISO-8859-1//TRANSLIT",$webdescription) . "'".",";
        $MyCmdStr = $MyCmdStr . "'". iconv("UTF-8","ISO-8859-1//TRANSLIT",$website) ."'";
        $MyCmdStr = $MyCmdStr . ');';
        $Result = odbc_exec($Quelle, $MyCmdStr);
        return $Result;
    }
}

?>