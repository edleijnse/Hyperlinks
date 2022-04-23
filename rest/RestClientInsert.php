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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["ID"])) {
        $IDErr = "ID is required";
    } else {
        $ID = $_POST["ID"];
    }
    if (empty($_POST["group"])) {
        $groupErrr = "group is required";
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
        $websiteErrr = "website is required";
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
<h2>Search hyperlinks</h2>
<p><span class="error">* required field</span></p>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    ID : <input type="text" name="ID" value="<?php echo $ID; ?>">
    <span class="error">* <?php echo $IDErr; ?></span>
    <br><br>
    group : <input type="text" name="group" value="<?php echo $group; ?>">
    <span class="error">* <?php echo $groupErr; ?></span>
    <br><br>
    category : <input type="text" name="category" value="<?php echo $category; ?>">
    <span class="error">* <?php echo $categoryErr; ?></span>
    <br><br>
    web description: <input type="text" name="webdescription" value="<?php echo $webdescription; ?>">
    <span class="error">* <?php echo $webdescriptionErr; ?></span>
    <br><br>
    website : <input type="text" name="website" value="<?php echo $website; ?>">
    <span class="error">* <?php echo $websiteErr; ?></span>
    <br><br>

    <input type="submit" name="submit" value="Submit">
</form>
<?php
echo "<h2>insert row with id:</h2>";
echo $ID;
echo "<br>";
if (empty($_POST["ID"])) {

} else {

    $url = 'http://192.168.0.54/hyperlinks/rest/Restcontroller.php/?command=insert';
    $url = $url . '&ID=' . $ID;
    $url = $url . '&category=' . $category;
    $url = $url . '&group=' . $group;
    $url = $url . '&webdescription=' . $webdescription;
    $url = $url . '&website=' . $website;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response_json = curl_exec($ch);
    echo("response_json: " . $response_json);
    curl_close($ch);
    $myResponce = "";
    $myResponce = json_decode($response_json, true);
    echo "<h2>Result</h2>";
    echo $myResponce;
}

?>
</body>
</html>
