<?php
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
$sql = "SELECT * FROM hyperlinks";
$result = mysqli_query($conn, $sql);
// $result = $conn->query($sql);
if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while($row = mysqli_fetch_assoc($result)) {
        echo ("ID: " . $row["ID"]. " - webgroup: " . $row["webgroup"]. " " . $row["webcategory"]. $row["webdescription"] . "<br>");
    }
} else {
    echo "0 results";
}

mysqli_close($conn);
?>
