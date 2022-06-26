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
$IDErr = $groupErr = $categoryErr = $webdescriptionErr = $websiteErr = $confirmErr = "";
$ID = $group = $category = $webdescription = $website = $confirm = "";
if (isset($_GET["ID"]))
    $ID = $_GET["ID"];
if (isset($_GET["group"]))
    $group = $_GET["group"];
if (isset($_GET["category"]))
    $category = $_GET["category"];
if (isset($_GET["webdescription"]))
    $webdescription = $_GET["webdescription"];
if (isset($_GET["website"]))
    $website = $_GET["website"];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["ID"])) {
        $IDErr = "ID is required";
    } else {
        //$ID = $_POST["ID"];
        // }
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
    if (empty($_POST["confirm"])) {
        $confirmErr = "confirmation required/already saved";
    } else {
        $confirm = $_POST["confirm"];
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
<h2>Update hyperlinks</h2>
<p><span class="error">* required field</span></p>
<form method="post" action="">
    ID______________: <input type="text" name="ID" value="<?php echo $ID; ?>">
    <span class="error">* <?php echo $IDErr; ?></span>
    <br><br>
    group___________: <input type="text" name="group" value="<?php echo $group; ?>">
    <span class="error">* <?php echo $groupErr; ?></span>
    <br><br>
    category_________: <input type="text" name="category" value="<?php echo $category; ?>">
    <span class="error">* <?php echo $categoryErr; ?></span>
    <br><br>
    web description___: <input type="text" name="webdescription" value="<?php echo $webdescription; ?>">
    <span class="error">* <?php echo $webdescriptionErr; ?></span>
    <br><br>
    website_________: <input type="text" name="website" value="<?php echo $website; ?>">
    <span class="error">* <?php echo $websiteErr; ?></span>
    <br><br>
    confirm_________: <input type="text" name="confirm" value="<?php echo $confirm; ?>">
    <span class="error">* <?php echo $confirmErr; ?></span>
    <br><br>

    <input type="submit" name="submit" value="Submit">
</form>
<?php
echo "<br>";
if ((empty($_POST["confirm"]))
    || (empty($_POST["group"]))
    || (empty($_POST["category"]))
    || (empty($_POST["webdescription"]))
    || (empty($_POST["website"]))) {
    echo "<h2>enter missing fields</h2>";
} else {
    echo "<h2>update row with id: ". $ID . "</h2>";
    $url = 'https://leijnse.info/hyperlinks/rest/Restcontroller.php/?command=updatemysql';
    $url = $url . '&ID=' . $ID;
    $url = $url . '&category=' . urlencode( $category);
    $url = $url . '&group=' . urlencode ($group);
    $url = $url . '&webdescription=' . urlencode($webdescription);
    $url = $url . '&website=' . urlencode($website);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response_json = curl_exec($ch);
    curl_close($ch);
    $myResponse = "";
    $myResponse = json_decode($response_json, true);
    echo "<h2>Result</h2>";
    echo $myResponse;
    $_POST["confirm"] = "";
}

?>
</body>
</html>