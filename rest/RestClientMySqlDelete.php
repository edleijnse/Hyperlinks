<!DOCTYPE HTML>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F8F8F8;
        }

        .error {
            color: #FF0000;
        }

        table {
            font-family: Arial, sans-serif;
            font-size: 24px;
            border-collapse: collapse;
            width: 80%;
            margin: auto;
            margin-top: 50px;
            box-shadow: 0px 0px 20px #CCC;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 12px;
            font-weight: normal;
            font-size: 24px;
        }

        h2 {
            text-align: center;
            color: #333333;
            margin-top: 50px;
        }

        form {
            text-align: center;
            margin-top: 50px;
            font-size: 24px;
        }

        input[type=text] {
            font-size: 24px;
            padding: 10px;
            border: none;
            border-bottom: 2px solid #333333;
        }

        input[type=submit] {
            font-size: 24px;
            padding: 10px 20px;
            border: none;
            color: white;
            background-color: #333333;
            cursor: pointer;
        }

        input[type=submit]:hover {
            background-color: #4CAF50;
        }

        tr:nth-child(even) {
            background-color: #e6e6e6;
        }

        a {
            color: #333;
            text-decoration: none;
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
echo "<h1><a style='color:green; font-weight:bold;' href=" . "https://tagger.biz/rest/RestClientMySqlInput.php/"
    . ">" . "search" . "</a></h1>";
?>
</body>
</html>
