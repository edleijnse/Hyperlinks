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
        $search = $_POST["search"];
        // check if name only contains letters and whitespace
        // if (!preg_match("/^[a-zA-Z-' ]*$/",$search)) {
        //    $searchErr = "Only letters and white space allowed";
        // }
    }

}
if (function_exists('test_input')) {
    echo "Function Exists";
} else {
    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

?>
<h2>Search hyperlinks</h2>
<p><span class="error">* required field</span></p>
<form method="post" action="">
    Search for: <input type="text" name="search" value="<?php echo $search; ?>">
    <span class="error">* <?php echo $searchErr; ?></span>
    <br><br>
    <input type="submit" name="submit" value="Submit">
</form>
<?php
echo "<h2>Searched for:</h2>";
echo $search;
echo "<br>";
$url = 'https://leijnse.info/hyperlinks/rest/Restcontroller.php/?command=allmysql&count=900&from=0&search=' . urlencode($search);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response_json = curl_exec($ch);
curl_close($ch);
$hyperlinks = array();
$hyperlinks = json_decode($response_json, true,512);
echo "<h2>Search Results</h2>";
echo "<table>";
foreach ($hyperlinks as $hyperlink) {
    if (isset($hyperlink['ID'])) {
        echo "<tr>";
        try {
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
            /* print($hyperlink['ID']) . ", " . $hyperlink['group'] . ", " . $hyperlink['category']
                 . ", " . $hyperlink['webdescription']
                 . ", " . $hyperlink['website']
                 . PHP_EOL;*/
            echo "<th>";
            echo "<a href=" .$hyperlink['website'] . ">" . "link" . "</a>";
            echo "</th>";
            echo "<th>";
            echo "<a href=" . "https://tagger.biz/hyperlink-update/?ID=".$hyperlink['ID']
                ."&category=".urlencode($hyperlink["category"])
                ."&group=".urlencode($hyperlink["group"])
                ."&webdescription=".urlencode($hyperlink["webdescription"])
                ."&website=".urlencode($hyperlink["website"])
                . ">" . "update" . "</a>";
            echo "</th>";
            echo "<th>";
            echo "<a href=" . "https://tagger.biz/hyperlink-delete-2/?ID=".$hyperlink['ID']
                . ">" . "delete" . "</a>";
            echo "</th>";

        } catch (TypeError $e) {

        } finally {

        }
        echo "</tr>";
    }
}
echo "</table>";
?>
</body>
</html>
