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
$IDErr = $groupErr = $categoryErr = $webdescriptionErr = $websiteErr = "";
$ID = $group = $category = $webdescription = $website = "";
if (isset($_GET["ID"]))
    $ID = $_GET["ID"];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["ID"])) {
        $IDErr = "ID is required";
    } else {
        $ID = $_POST["ID"];
    }
    if (empty($_POST["group"])) {
        $groupErr = "group is required";
    } else {
        $group = $_POST["group"];
        // }
    }
    if (empty($_POST["category"])) {
        $categoryErr = "category is required";
    } else {
        $category = $_POST["category"];
    }
    if (empty($_POST["webdescription"])) {
        $webdescriptionErr = "webdescription is required";
    } else {
        $webdescription = $_POST["webdescription"];
    }
    if (empty($_POST["website"])) {
        $websiteErr = "website is required";
    } else {
        $website = $_POST["website"];
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
<h2>Delete hyperlinks</h2>
<p><span class="error">* required field</span></p>
<form method="post" action="">
    ID______________: <input type="text" name="ID" value="<?php echo $ID; ?>">
    <span class="error">* <?php echo $IDErr; ?></span>
    <br><br>

    <input type="submit" name="submit" value="Submit">
</form>
<?php
echo "<br>";
if (empty($_POST["ID"])) {
    echo "<h2>enter missing fields</h2>";
} else {
    echo "<h2>delete row with id: ". $ID . "</h2>";
    $url = 'https://leijnse.info/hyperlinks/rest/Restcontroller.php/?command=deletemysql';
    $url = $url . '&ID=' . $ID;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response_json = curl_exec($ch);
    curl_close($ch);
    $myResponce = "";
    $myResponce = json_decode($response_json, true);
    echo "<h2>Result</h2>";
    echo $myResponce;
}
echo "<br><br>";
echo "<a href=" . "https://tagger.biz/rest/RestClientMySqlInput.php/"
    . ">" . "search" . "</a>";
?>
</body>
</html>
