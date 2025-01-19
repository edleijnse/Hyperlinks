<!DOCTYPE html>
<html>

<head>
    <?php include 'head.php'; ?>
    <style>
        .large-font {
            font-size: 34px;
        }
        .header-font {
            font-size: 24px;
        }
        /* Add CSS here */
        #scrollToTop {
            position: fixed;
            bottom: 20px;
            left: 40px;
            display: block; /* Always visible */
            background-color: #000;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            z-index: 1000;
        }

        #scrollToTop:hover {
            background-color: #444;
        }
    </style>
</head>
<body>
<?php

// Database configuration
$dbFile = 'LightroomCatalog2023-02-v13-3 Excire.sqlite';

try {
    // Connect to the SQLite database
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Error connecting to SQLite database: " . $e->getMessage());
}

// Handle request
$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['ID'] ?? '';
            $webgroup = $_POST['webgroup'] ?? '';
            $webcategory = $_POST['webcategory'] ?? '';
            $webdescription = $_POST['webdescription'] ?? '';
            $website = $_POST['website'] ?? '';

            createHyperlink($pdo, $id, $webgroup, $webcategory, $webdescription, $website);
        } else {
            displayCreateForm();
        }
        break;

    case 'read':
        if ($id) {
            $link = readHyperlink($pdo, $id);
            displayHyperlink($link);
        } else {
            $links = readAllHyperlinks($pdo);
            displayHyperlinkList($links);
        }
        break;

    case 'update':
        if ($id && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $webgroup = $_POST['webgroup'] ?? '';
            $webcategory = $_POST['webcategory'] ?? '';
            $webdescription = $_POST['webdescription'] ?? '';
            $website = $_POST['website'] ?? '';
            updateHyperlink($pdo, $id, $webgroup, $webcategory, $webdescription, $website);
        } elseif ($id) {
            $link = readHyperlink($pdo, $id);
            displayUpdateForm($link);
        }
        break;

    case 'delete':
        if ($id && $_SERVER['REQUEST_METHOD'] === 'POST') {
            deleteHyperlink($pdo, $id);
        } elseif ($id) {
            displayDeleteForm($id);
        }
        break;

    default:
        // Default action: display all links
        // display a search form to allow users to input search filters

    echo "<h2>Search Hyperlinks</h2>";
        echo "<form method='get'>
        <label>Webgroup: <input type='text' class='input' name='webgroup' value='" . htmlspecialchars($_GET['webgroup'] ?? '') . "'></label><br>
        <label>Webcategory: <input type='text' class='input' name='webcategory' value='" . htmlspecialchars($_GET['webcategory'] ?? '') . "'></label><br>
        <label>Webdescription: <input type='text' class='input' name='webdescription' value='" . htmlspecialchars($_GET['webdescription'] ?? '') . "'></label><br>
        <label>Website: <input type='text' class='input' name='website' value='" . htmlspecialchars($_GET['website'] ?? '') . "'></label><br>
        <input type='submit' value='Search' class='ask'>
        <a href='?" . htmlspecialchars(http_build_query([])) . "'>
            <button type='button' class='ask'>Clear All</button>
        </a>
      </form>";

        $searchCriteria = [];
        if (!empty($_GET['webgroup'])) {
            $searchCriteria['webgroup'] = htmlspecialchars($_GET['webgroup']);
        }
        if (!empty($_GET['webcategory'])) {
            $searchCriteria['webcategory'] = htmlspecialchars($_GET['webcategory']);
        }
        if (!empty($_GET['webdescription'])) {
            $searchCriteria['webdescription'] = htmlspecialchars($_GET['webdescription']);
        }
        if (!empty($_GET['website'])) {
            $searchCriteria['website'] = htmlspecialchars($_GET['website']);
        }

// Call function with user-provided criteria
        $links = readAllHyperlinks($pdo, $searchCriteria);
        displayHyperlinkList($links);

}

// CRUD Functions

function createHyperlink($pdo, $id, $webgroup, $webcategory, $webdescription, $website): void
{
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO hyperlinks (ID, webgroup, webcategory, webdescription, website) VALUES (:ID, :webgroup, :webcategory, :webdescription, :website)");
    $stmt->execute([
        ':ID' => $id,
        ':webgroup' => $webgroup,
        ':webcategory' => $webcategory,
        ':webdescription' => $webdescription,
        ':website' => $website
    ]);
    $pdo->commit();
    echo "Hyperlink created successfully!";
}

function readHyperlink($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT * FROM hyperlinks WHERE ID = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function readAllHyperlinks(PDO $database, array $searchCriteria = []): array
{
    // Base query
    $query = "SELECT * FROM hyperlinks";

    // Track WHERE conditions and parameters for binding
    $conditions = [];
    $parameters = [];

    // Dynamically build WHERE clause based on search criteria
    foreach ($searchCriteria as $column => $value) {
        $conditions[] = "$column LIKE :$column";
        $parameters[":$column"] = "%$value%"; // Use wildcards for partial matching
    }

    // Add WHERE clause if conditions exist
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    $query .= " ORDER BY webgroup, webcategory, webdescription, website";
        // Prepare and execute quer
    $statement = $database->prepare($query);
    $statement->execute($parameters);

    // Fetch and return all matching rows
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function updateHyperlink($pdo, $id, $webgroup, $webcategory, $webdescription, $website)
{
    $pdo->beginTransaction();
    $query = "UPDATE hyperlinks 
              SET webgroup = :webgroup, 
                  webcategory = :webcategory, 
                  webdescription = :webdescription, 
                  website = :website 
              WHERE ID = :id";

    $statement = $pdo->prepare($query);
    $statement->execute([
        ':webgroup' => $webgroup,
        ':webcategory' => $webcategory,
        ':webdescription' => $webdescription,
        ':website' => $website,
        ':id' => $id
    ]);
    $pdo->commit();

    echo "Hyperlink updated successfully!";
}

function deleteHyperlink($pdo, $id)
{
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("DELETE FROM hyperlinks WHERE ID = :id");
    $stmt->execute([':id' => $id]);
    $pdo->commit();
    echo "Hyperlink deleted successfully!";
}

// HTML Display Functions

function displayHyperlinkList($links)
{
    echo "<h1>Hyperlinks List</h1>";
    echo "<a href='?action=create'>Create New Hyperlink</a>";
    echo "<ul>";
    foreach ($links as $link) {
        echo "<li>" . htmlspecialchars($link['webgroup']) . " (" . htmlspecialchars($link['webcategory']) . ") - " . htmlspecialchars($link['webdescription']) . "<br>";
        echo "Website: <a href='" . htmlspecialchars($link['website']) . "'>" . htmlspecialchars($link['website']) . "</a>";
        echo " [<a href='?action=read&id=" . $link['ID'] . "'>View</a>]";
        echo " [<a href='?action=update&id=" . $link['ID'] . "'>Edit</a>]";
        echo " [<a href='?action=delete&id=" . $link['ID'] . "'>Delete</a>]";
        echo "</li>";
    }
    echo "</ul>";
}


function displayHyperlink($link)
{
    echo "<h1>Hyperlink Details</h1>";
    echo "<p>Webgroup: " . htmlspecialchars($link['webgroup']) . "</p>";
    echo "<p>Webcategory: " . htmlspecialchars($link['webcategory']) . "</p>";
    echo "<p>Webdescription: " . htmlspecialchars($link['webdescription']) . "</p>";
    echo "<p>Website: <a href='" . htmlspecialchars($link['website']) . "'>" . htmlspecialchars($link['website']) . "</a></p>";
    echo "<p>ID: " . htmlspecialchars($link['ID']) . "</p>";
    echo "<a href='?'>Back to list</a>";
}

function displayCreateForm()
{
    echo "<h1>Create Hyperlink</h1>";
    echo "<form method='post'>
            <label>Webgroup: <input type='text'  class='ask' name='webgroup'></label><br>
            <label>Webcategory: <input type='text'  class='ask' name='webcategory'></label><br>
            <label>Webdescription: <input type='text'  class='ask' name='webdescription'></label><br>
            <label>Website: <input type='url'  class='ask' name='website'></label><br>
            <label>ID: <input type='text'  class='ask' name='ID'></label><br>
            <input type='submit'  class='ask' value='Create'>
          </form>";
}
function displayUpdateForm($link)
{
    echo "<h1>Update Hyperlink</h1>";
    echo "<form method='post'>
            <label>Webgroup: <input type='text' name='webgroup' class='ask' value='" . htmlspecialchars($link['webgroup']) . "'></label><br>
            <label>Webcategory: <input type='text' name='webcategory'  class='ask' value='" . htmlspecialchars($link['webcategory']) . "'></label><br>
            <label>Webdescription: <input type='text' name='webdescription'  class='ask' value='" . htmlspecialchars($link['webdescription']) . "'></label><br>
            <label>Website: <input type='url' name='website'  class='ask' value='" . htmlspecialchars($link['website']) . "'></label><br>
            <input type='submit'  class='ask' value='Update'>
          </form>";
}

function displayDeleteForm($id)
{
    echo "<h1>Delete Hyperlink</h1>";
    echo "<p>Are you sure you want to delete this hyperlink?</p>";
    echo "<form method='post'>
            <input type='submit' value='Yes, delete'>
          </form>";
}
?>
</body>

</html>