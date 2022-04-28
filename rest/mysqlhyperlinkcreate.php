<?php
// https://www.w3schools.com/php/php_mysql_select.asp
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
    $sql = "CREATE TABLE tabelle (
      id INT AUTO_INCREMENT PRIMARY KEY,
      feld VARCHAR(255)
    )";
    $result = mysqli_query($conn, $sql);
    echo "Tabelle angelegt.<br />";

    $sql = "INSERT INTO tabelle (feld) VALUES ('Wert1')";
    $result = mysqli_query($conn, $sql);
    echo "Daten eingetragen 1.<br />";

    $sql = "INSERT INTO tabelle (feld) VALUES ('Wert2')";
    $result = mysqli_query($conn, $sql);
    echo "Daten eingetragen 2.<br />";

    mysqli_close($conn);
} catch (Exception $ex) {
    echo "Fehler: " . $ex->getMessage();
}
?>
