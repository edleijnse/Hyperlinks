<!DOCTYPE HTML>
<html>
<head>
    <style>
        .error {
            color: #FF0000;
        }

        table {
            font-family: arial, sans-serif;
            font-weight: normal;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #dddddd;
            font-weight: normal;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
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
        $url = 'https://leijnse.info/hyperlinks/rest/Restcontroller.php/?command=allmysql&count=900&from=0&search=' . urlencode($search);
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
                echo "<a href=" . $hyperlink['website'] . ">" . $hyperlink['webdescription'] . "</a>";
                echo "</th>";
                echo "<th>";
                echo "<a href=" . "https://tagger.biz/hyperlink-update/?ID=" . $hyperlink['ID']
                    . "&category=" . urlencode($hyperlink["category"])
                    . "&group=" . urlencode($hyperlink["group"])
                    . "&webdescription=" . urlencode($hyperlink["webdescription"])
                    . "&website=" . urlencode($hyperlink["website"])
                    . ">" . "update" . "</a>";
                echo "</th>";
                echo "<th>";
                echo "<a href=" . "https://tagger.biz/hyperlink-delete-2/?ID=" . $hyperlink['ID']
                    . ">" . "delete" . "</a>";
                echo "</th>";

            } catch (TypeError $e) {

            } finally {

            }
            echo "</tr>";
        }
    }
    echo "</table>";
}
?>
</body>
</html>