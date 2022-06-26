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

    public function processRsHyperLinks($rs, $myFrom, $myCount): array
    {
        $hyperlinks = array();
        // echo "execute OK";
        $iiCount = 0;
        $iiFrom = 0;
        while (!$rs->EOF) {
            // echo "record found";
            if ($iiFrom >= $myFrom) {
                if ($iiCount < $myCount) {
                    // print iconv("ISO-8859-1","UTF-8",$rs->fields[1]). ' ' . $rs->fields[1] . ' ' . $rs->fields[2] . ' ' . $rs->fields[3] . '<BR>';
                    $hyperLink1 = new HyperLink();
                    $hyperLink1->ID = $rs->fields[0];
                    $mygroup = $rs->fields[1];
                    $mygroupconv = iconv("ISO-8859-1", "UTF-8", $mygroup);
                    $hyperLink1->group = $mygroupconv;
                    $mycategory = $rs->fields[2];
                    $mycategoryconv = iconv("ISO-8859-1", "UTF-8", $mycategory);
                    $hyperLink1->category = $mycategoryconv;
                    $mywebsitedescription = $rs->fields[3];
                    $mywebsitedescriptionconv = iconv("ISO-8859-1", "UTF-8", $mywebsitedescription);
                    $hyperLink1->webdescription = $mywebsitedescriptionconv;
                    $mywebsite = $rs->fields[4];
                    $mywebsiteconv = iconv("ISO-8859-1", "UTF-8", $mywebsite);
                    $hyperLink1->website = $mywebsiteconv;
                    $hyperlinks[$iiCount] = $hyperLink1;
                }
                $iiCount++;
            }
            $iiFrom++;
            $rs->MoveNext();  //  Moves to the next row
        }  // end while
        return $hyperlinks;
    }

    public function getAllHyperlinks($myCount, $myFrom, $mySearch): array
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
        $sql = "";
        if (empty($mySearch)) {
            $sql = "SELECT * from hyperlinks order by group, category, webdescription, website ";
        } else {
            $sql = "SELECT * from hyperlinks ";
            $sql = $sql . " WHERE ";
            $sql = $sql . "(group like ";
            $sql = $sql . "'%";
            $sql = $sql . trim($mySearch);
            $sql = $sql . "%') ";
            $sql = $sql . " OR ";
            $sql = $sql . "(category like ";
            $sql = $sql . "'%";
            $sql = $sql . trim($mySearch);
            $sql = $sql . "%') ";
            $sql = $sql . " OR ";
            $sql = $sql . "(webdescription like ";
            $sql = $sql . "'%";
            $sql = $sql . trim($mySearch);
            $sql = $sql . "%') ";
            $sql = $sql . " OR ";
            $sql = $sql . "(website like ";
            $sql = $sql . "'%";
            $sql = $sql . trim($mySearch);
            $sql = $sql . "%') ";
            $sql = $sql . "order by group, category, webdescription, website;";
        }

        $rs = $db->Execute($sql);
        if (!$rs) {
            // print $db->ErrorMsg(); // Displays the error message if no results could be returned
            // echo ErrorMsg();
        } else {
            // echo "execute OK"
            $hyperlinks = $this->processRsHyperLinks($rs, $myFrom, $myCount);

        } // end else

        return $hyperlinks;
    }

    public function getAllHyperlinksMySql($myCount, $myFrom, $mySearch): array
    {
        $servername = "mysql2.webland.ch";
        $username = "leijn_hyperlinks";
        $password = "XarDam09;09DamXar";
        $dbname = "leijn_hyperlinks";
        $conn = mysqli_connect($servername, $username, $password, $dbname);
        $sql = "";
        if (empty($mySearch)) {
            $sql = "SELECT * from hyperlinks order by webgroup, webcategory, webdescription, website ";
        } else {
            $sql = "SELECT * from hyperlinks ";
            $sql = $sql . " WHERE ";
            $sql = $sql . "(webgroup like ";
            $sql = $sql . "'%";
            $sql = $sql . trim($mySearch);
            $sql = $sql . "%') ";
            $sql = $sql . " OR ";
            $sql = $sql . "(webcategory like ";
            $sql = $sql . "'%";
            $sql = $sql . trim($mySearch);
            $sql = $sql . "%') ";
            $sql = $sql . " OR ";
            $sql = $sql . "(webdescription like ";
            $sql = $sql . "'%";
            $sql = $sql . trim($mySearch);
            $sql = $sql . "%') ";
            $sql = $sql . " OR ";
            $sql = $sql . "(website like ";
            $sql = $sql . "'%";
            $sql = $sql . trim($mySearch);
            $sql = $sql . "%') ";
            $sql = $sql . "order by webgroup, webcategory, webdescription, website;";
        }
        $result = mysqli_query($conn, $sql);
        $hyperlinks = array();
        $iiCount = 0;
        $iiFrom = 0;
        if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while ($row = mysqli_fetch_assoc($result)) {
                // echo ("ID: " . $row["ID"]. " - webgroup: " . $row["webgroup"]. " " . $row["webcategory"]. $row["webdescription"] . "<br>");

                if ($iiFrom >= $myFrom) {
                    if ($iiCount < $myCount) {
                        $hyperLink1 = new HyperLink();
                        $hyperLink1->ID = $row["ID"];
                        $mygroup = $row["webgroup"];
                        $hyperLink1->group = $mygroup;
                        $mycategory = $row["webcategory"];
                        $hyperLink1->category = $mycategory;
                        $mywebsitedescription = $row["webdescription"];
                        $hyperLink1->webdescription = $mywebsitedescription;
                        $mywebsite = $row["website"];
                        $hyperLink1->website = $mywebsite;
                        $hyperlinks[$iiCount] = $hyperLink1;
                        $iiCount++;
                    }
                }
                $iiFrom++;
            }
        } else {
            echo "0 results";
        }


        return $hyperlinks;
    }

    public function getHyperlinkMySql($id): array
    {
        $servername = "mysql2.webland.ch";
        $username = "leijn_hyperlinks";
        $password = "XarDam09;09DamXar";
        $dbname = "leijn_hyperlinks";
        $conn = mysqli_connect($servername, $username, $password, $dbname);
        $sql = "";
        $sql = "SELECT * from hyperlinks ";
        $sql = $sql . " WHERE ";
        $sql = $sql . "(ID = ";
        $sql = $sql . trim($id);
        $sql = $sql . ") ";
        $sql = $sql . "order by webgroup, webcategory, webdescription, website;";
        $result = mysqli_query($conn, $sql);
        $hyperlinks = array();
        $iiCount = 0;
        if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while ($row = mysqli_fetch_assoc($result)) {
                $hyperLink1 = new HyperLink();
                $hyperLink1->ID = $row["ID"];
                $mygroup = $row["webgroup"];
                $mygroupconv = iconv("ISO-8859-1", "UTF-8", $mygroup);
                $hyperLink1->group = $mygroupconv;
                $mycategory = $row["webcategory"];
                $mycategoryconv = iconv("ISO-8859-1", "UTF-8", $mycategory);
                $hyperLink1->category = $mycategoryconv;
                $mywebsitedescription = $row["webdescription"];
                $mywebsitedescriptionconv = iconv("ISO-8859-1", "UTF-8", $mywebsitedescription);
                $hyperLink1->webdescription = $mywebsitedescriptionconv;
                $mywebsite = $row["website"];
                $mywebsiteconv = iconv("ISO-8859-1", "UTF-8", $mywebsite);
                $hyperLink1->website = $mywebsiteconv;
                $hyperlinks[$iiCount] = $hyperLink1;
                $iiCount++;
            }
        } else {
            echo "0 results";
        }


        return $hyperlinks;
    }


    public function getHyperlink($id): array
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
        $sql = "";

        $sql = "SELECT * from hyperlinks ";
        $sql = $sql . " WHERE ";
        $sql = $sql . "(ID = ";
        $sql = $sql . trim($id);
        $sql = $sql . ") ";
        $sql = $sql . "order by group, category, webdescription, website;";


        $rs = $db->Execute($sql);
        if (!$rs) {
            print $db->ErrorMsg(); // Displays the error message if no results could be returned
            // echo ErrorMsg();
        } else {
            // echo "execute OK"
            $myFrom = 0;
            $myCount = 100;
            $hyperlinks = $this->processRsHyperLinks($rs, $myFrom, $myCount);

        } // end else

        return $hyperlinks;
    }

    public function deleteHyperlink($ID)
    {
        $myadodbpath = $this->adodb_path;
        $mycfg_dsn = $this->cfg_dsn;
        include($myadodbpath . "/adodb.inc.php"); // includes the adodb library
        include($myadodbpath . "/drivers/adodb-odbc.inc.php"); // includes the odbc driver
        $Quelle = odbc_connect($mycfg_dsn, "", "");
        $MyCmdStr = "DELETE FROM hyperlinks WHERE (ID = ";
        $MyCmdStr = $MyCmdStr . $ID . "); ";
        $Result = odbc_exec($Quelle, $MyCmdStr);
        return $Result;
    }

    public function deleteHyperlinkMySql($ID)
    {
        try {
            $servername = "mysql2.webland.ch";
            $username = "leijn_hyperlinks";
            $password = "XarDam09;09DamXar";
            $dbname = "leijn_hyperlinks";
            $conn = mysqli_connect($servername, $username, $password, $dbname);
            if (!$conn) {
                echo("Connection failed: " . mysqli_connect_error());
            }
            $MyCmdStr = "DELETE FROM hyperlinks WHERE (ID = ";
            $MyCmdStr = $MyCmdStr . $ID . "); ";
            $result = mysqli_query($conn, $MyCmdStr);

            mysqli_close($conn);
            return $result;
        } catch (Exception $ex) {
            echo "Fehler: " . $ex->getMessage();
        }
    }

    public function insertHyperlink($ID, $group, $category, $webdescription, $website)
    {
        $myadodbpath = $this->adodb_path;
        $mycfg_dsn = $this->cfg_dsn;
        include($myadodbpath . "/adodb.inc.php"); // includes the adodb library
        include($myadodbpath . "/drivers/adodb-odbc.inc.php"); // includes the odbc driver
        $Quelle = odbc_connect($mycfg_dsn, "", "");
        $MyCmdStr = "INSERT INTO hyperlinks  VALUES(";
        $MyCmdStr = $MyCmdStr . $ID . ", ";
        $MyCmdStr = $MyCmdStr . "'" . iconv("UTF-8", "ISO-8859-1//TRANSLIT", $group) . "'" . ",";
        $MyCmdStr = $MyCmdStr . "'" . iconv("UTF-8", "ISO-8859-1//TRANSLIT", $category) . "'" . ",";
        $MyCmdStr = $MyCmdStr . "'" . iconv("UTF-8", "ISO-8859-1//TRANSLIT", $webdescription) . "'" . ",";
        $MyCmdStr = $MyCmdStr . "'" . iconv("UTF-8", "ISO-8859-1//TRANSLIT", $website) . "'";
        $MyCmdStr = $MyCmdStr . ');';
        $Result = odbc_exec($Quelle, $MyCmdStr);
        return $Result;
    }

    public function insertHyperlinkMySql($ID, $group, $category, $webdescription, $website)
    {
        try {
            $servername = "mysql2.webland.ch";
            $username = "leijn_hyperlinks";
            $password = "XarDam09;09DamXar";
            $dbname = "leijn_hyperlinks";
            $conn = mysqli_connect($servername, $username, $password, $dbname);
            if (!$conn) {
                echo("Connection failed: " . mysqli_connect_error());
            }
            $MyCmdStr = "INSERT INTO hyperlinks ( `webgroup`, `webcategory`, `webdescription`, `website`)  VALUES(";
            // $MyCmdStr = $MyCmdStr . $ID . ", ";
            $MyCmdStr = $MyCmdStr . "'" .  $group . "'" . ",";
            $MyCmdStr = $MyCmdStr . "'" .  $category . "'" . ",";
            $MyCmdStr = $MyCmdStr . "'" .  $webdescription . "'" . ",";
            $MyCmdStr = $MyCmdStr . "'" .  $website . "'";
            $MyCmdStr = $MyCmdStr . ');';
            $result = mysqli_query($conn, $MyCmdStr);

            mysqli_close($conn);
            return $result;
        } catch (Exception $ex) {
            echo "Fehler: " . $ex->getMessage();
        }
    }
    public function updateHyperlinkMySql($ID, $group, $category, $webdescription, $website)
    {
        try {
            $servername = "mysql2.webland.ch";
            $username = "leijn_hyperlinks";
            $password = "XarDam09;09DamXar";
            $dbname = "leijn_hyperlinks";
            $conn = mysqli_connect($servername, $username, $password, $dbname);
            if (!$conn) {
                echo("Connection failed: " . mysqli_connect_error());
            }
            $MyCmdStr = "UPDATE `hyperlinks` SET ";
            $MyCmdStr = $MyCmdStr . "`webgroup` = " . "'" . $group . "'";
            $MyCmdStr = $MyCmdStr . ", ";
            $MyCmdStr = $MyCmdStr . "`webcategory` = " . "'" . $category . "'";
            $MyCmdStr = $MyCmdStr . ", ";
            $MyCmdStr = $MyCmdStr . "`webdescription` = " . "'" . $webdescription. "'";
            $MyCmdStr = $MyCmdStr . ", ";
            $MyCmdStr = $MyCmdStr . "`website` = " . "'" . $website. "'";
            $MyCmdStr = $MyCmdStr   ." WHERE `hyperlinks`.`ID` = " .$ID;
            $result = mysqli_query($conn, $MyCmdStr);

            mysqli_close($conn);
            return $result;
        } catch (Exception $ex) {
            echo "Fehler: " . $ex->getMessage();
        }
    }
}

?>