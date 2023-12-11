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
            padding: 10px;
            border: none;
            border-bottom: 2px solid #333333;
            font-size: 24px;
        }

        input[type=submit] {
            padding: 10px 20px;
            border: none;
            color: white;
            background-color: #333333;
            cursor: pointer;
            font-size: 24px;
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
$IDErr = $groupErr = $categoryErr = $webdescriptionErr = $websiteErr = $confirmErr = "";
$ID = $group = $category = $webdescription = $website =  "";
$confirm = "yes";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
<h2>Insert hyperlinks</h2>
<p><span class="error">* required field</span></p>
<form method="post" action="">
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
    echo "<h2>insert row with id: ". $ID . "</h2>";
    $url = 'https://leijnse.info/hyperlinks/rest/Restcontroller.php/?command=insertmysql';
    $url = $url . '&category=' . urlencode( $category);
    $url = $url . '&group=' . urlencode ($group);
    $url = $url . '&webdescription=' . urlencode($webdescription);
    $url = $url . '&website=' . urlencode($website);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response_json = curl_exec($ch);
    curl_close($ch);
    $myResponce = "";
    $myResponce = json_decode($response_json, true);
    echo "<h2>Result</h2>";
    echo $myResponce;
    $_POST["confirm"] = "";
}
echo "<br><br>";
echo "<h1><a style='color:green; font-weight:bold;' href=" . "https://tagger.biz/rest/RestClientMySqlInput.php/"
    . ">" . "search" . "</a></h1>";
?>
</body>
</html>