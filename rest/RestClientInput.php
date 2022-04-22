<!DOCTYPE HTML>
<html>
<head>
    <style>
        .error {
            color: #FF0000;
        }
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
    </style>
</head>
<body>
<?php
$searchErr = "";
$search = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["search"])) {
        $nameErr = "search is required";
    } else {
        $search = test_input($_POST["search"]);
        // check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z-' ]*$/",$search)) {
            $searchErr = "Only letters and white space allowed";
        }
    }

}
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
<h2>Search hyperlinks</h2>
<p><span class="error">* required field</span></p>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    Search for: <input type="text" name="search" value="<?php echo $search;?>">
    <span class="error">* <?php echo $searchErr;?></span>
    <br><br>
    <input type="submit" name="submit" value="Submit">
</form>
<?php
echo "<h2>Searched for:</h2>";
echo $search;
echo "<br>";
$url = 'https://leijnse.info/hyperlinks/rest/Restcontroller.php/?command=all&count=900&from=0&search=' . $search;
//$url = 'http://192.168.0.210/hyperlinks/rest/Restcontroller.php/?command=all&count=900&from=0&search=' . $search;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response_json = curl_exec($ch);
curl_close($ch);
$hyperlinks = array();
$hyperlinks = json_decode($response_json, true);
echo "<h2>Search Results</h2>";
echo "<table>";
foreach ($hyperlinks as $hyperlink) {
    echo "<tr>";
    echo "<th>";
    echo $hyperlink['ID'];
    echo "</th>";
    echo "<th>";
    echo $hyperlink['group'];
    echo "</th>";
    echo "<th>";
    echo $hyperlink['category'];
    echo "</th>";
    echo "<th>";
    echo $hyperlink['webdescription'];
    echo "</th>";
    echo "<th>";
    echo "<a href=" . $hyperlink['website'] . ">" . $hyperlink['website'] . "</a>";
   // echo $hyperlink['website'];
    echo "</th>";
   /* print($hyperlink['ID']) . ", " . $hyperlink['group'] . ", " . $hyperlink['category']
        . ", " . $hyperlink['webdescription']
        . ", " . $hyperlink['website']
        . PHP_EOL;*/
    echo "</tr>";
}
echo "</table>";
?>
</body>
</html>
