<html>
<head>
    <title>Suchen Thema Textart</title>
</head>
<body>
<form method="post" action="maint_details.php">
    <input name="Suchtext" type="text" value="<? include("initsuchtext.php"); ?>" size="22">
    <input type="submit" value="Text suchen"
    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="checkbox" name="checkCompleteText" <? include("initcheckcompletetext.php"); ?>>
        Suchergebnis UND Uebersicht&nbsp;&nbsp; </p>
</form>
<?php
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

// $Ergebnis = odbc_exec($Quelle, "SELECT * from vhyperlinksmaintenance");
include("tablesearch.php");
// sendtable($Quelle, $Ergebnis, "editform.php");

$database_type = "access";
$host = $cfg_dsn;
$user = "";
$password = "";
$database_name = "hyperlinks.mdb";

$db = NewADOConnection("$database_type"); // A new connection
$db->Connect("$host", "$user", "$password", "$database_name");
echo "CONNECTED";
$sql = "SELECT * from vhyperlinks";
$rs = &$db->Execute($sql);
if (!$rs) {
    print $db->ErrorMsg(); // Displays the error message if no results could be returned
    echo ErrorMsg();
} else {
    echo "execute OK";
    while (!$rs->EOF) {
        // echo "record found";
        print $rs->fields[0] . ' ' . $rs->fields[1] . ' ' . $rs->fields[2] . ' ' . $rs->fields[3] . '<BR>';
        // fields[0] is surname, fields[1] is age
        $rs->MoveNext();  //  Moves to the next row
    }  // end while
} // end else


?>

</p>
<body bgcolor="#FFFFF2">
<body>
</html>