<?php
// $servername = "{SERVERNAME}";
$servername = "192.168.0.37";
//$username = "{USERNAME}";
$username = "edlei";
// $password = "{PASSWORD}";
$password = "xxxxx";
$dbname = "mysql";

try {
    // Create PDO instance
    $conn = new PDO("mysql:host={$servername};dbname={$dbname}", $username, $password);

    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL Query
    $sql = "SELECT * FROM hyperlinks";

    // Prepare statement
    $stmt = $conn->prepare($sql);

    // Execute the statement
    $stmt->execute();

    // Fetch all result
    $hyperlinks = $stmt->fetchAll();

    // Display the result
    foreach($hyperlinks as $hyperlink) {
        echo "ID: " . $hyperlink['ID'] . ", Webgroup: " . $hyperlink['webgroup'] . ", Webcategory: " . $hyperlink['webcategory'] . ", Webdescription: " . $hyperlink['webdescription'] . ", Website: " . $hyperlink['website'] . "\n";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>