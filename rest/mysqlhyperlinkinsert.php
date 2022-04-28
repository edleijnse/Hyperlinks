<?php
try {
    $servername = "mysql2.webland.ch";
    $username = "leijn_hyperlinks";
    $password = "XarDam09;09DamXar";
    $dbname = "leijn_hyperlinks";
//   $db = new MySQLi("mysql2.webland.ch", "XarDam09;09DamXar", "leijn_hyperlinks", "PHP");
//
// Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
    if (!$conn) {
        echo("Connection failed: " . mysqli_connect_error());
    }
    $sql = "INSERT INTO hyperlinks(`ID`, `webgroup`, `webcategory`, `webdescription`, `website`) VALUES (3001,'IT','PHP','insert test 2','https://letsdance')";
    $result = mysqli_query($conn, $sql);

    mysqli_close($conn);
} catch (Exception $ex) {
    echo "Fehler: " . $ex->getMessage();
}
?>
