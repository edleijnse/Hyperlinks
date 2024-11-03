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
            font-size: 24px;
            text-align: left;
            padding: 12px;
            font-weight: normal;
        }

        h2 {
            text-align: center;
            color: #333333;
            margin-top: 50px;
        }

        form {
            text-align: center;
            margin-top: 50px;
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
        @media screen and (max-width: 1000px) {
            body {
                font-size: 18px; // smaller font-size for mobile
            }

            table {
                width: 100%; // table should take full width on mobile
            }
            td, th {
                padding: 8px; // lesser padding on mobile
            }

            form {
                margin-top: 20px; // lesser margin on mobile
            }
        }

    </style>
</head>
<body>
<h2>Search hyperlinks</h2>

<form method="post" action="">
    Search for: <input type="text" name="search" value="<?php echo htmlspecialchars($_POST["search"] ?? ''); ?>">
    <br><br>
    <input type="submit" name="submit" value="Submit">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["search"])) {

    // call function with $url as parameter
    $search = filter_input(INPUT_POST, 'search', FILTER_SANITIZE_STRING);
    echo "<h2>Searched for: $search</h2>";
    $url = 'http://localhost/rest/Restcontroller.php/?command=allmysql&count=900&from=0&search=' . urlencode($search);
    $hyperlinks = fetchHyperlinks($url);
    if ($hyperlinks === null) {
        // retry with other URL
        $url = 'https://tagger.biz/hyperlinks/rest/Restcontroller.php/?command=allmysql&count=900&from=0&search=' . urlencode($search);
        $hyperlinks = fetchHyperlinks($url);
    }
    if ($hyperlinks === null) {
        echo "Error fetching or decoding hyperlinks.";
    } else {
        displayHyperlinksTable($hyperlinks);
    }
}
function fetchHyperlinks($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response_json = curl_exec($ch);
    curl_close($ch);
    return json_decode($response_json, true, 512);
}


function displayHyperlinksTable($hyperlinks) {
    echo "<h2>Search Results</h2>";
    echo "<table>";

    foreach ($hyperlinks as $hyperlink) {
        if (isset($hyperlink['ID'])) {
            echo "<tr>";
            try {
                echo "<th>";
                echo $hyperlink['group'];
                echo "</th>";
                echo "<th>";
                echo $hyperlink['category'];
                echo "</th>";
                echo "<th>";
                echo "<a style='color:blue' href=" . $hyperlink['website'] . ">" . $hyperlink['webdescription'] . "</a>";
                echo "</th>";
                echo "<th>";
                echo "<a style='color:green' href=" . "https://tagger.biz/rest/RestClientMySqlUpdate.php/?ID=" . $hyperlink['ID']
                    . "&category=" . urlencode($hyperlink["category"])
                    . "&group=" . urlencode($hyperlink["group"])
                    . "&webdescription=" . urlencode($hyperlink["webdescription"])
                    . "&website=" . urlencode($hyperlink["website"])
                    . ">" . "update" . "</a>";
                echo "</th>";
                echo "<th>";
                echo "<a style='color:red' href=" . "https://tagger.biz/rest/RestClientMySqlDelete.php/?ID=" . $hyperlink['ID']
                    . ">" . "delete" . "</a>";
                echo "</th>";

            } catch (TypeError $e) {

            } finally {

            }
            echo "</tr>";
        }
    }
    echo "</table>";
    echo "<br><br>";
    echo "<a style='color:green; font-weight:bold;'  href=" . "https://tagger.biz/rest/RestClientMySqlInsert.php/"
        . ">" . "insert new" . "</a>";
}
?>
</body>
</html>