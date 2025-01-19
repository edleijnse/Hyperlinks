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
            $name = $_POST['name'] ?? '';
            $url = $_POST['url'] ?? '';
            createHyperlink($pdo, $name, $url);
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
            $name = $_POST['name'] ?? '';
            $url = $_POST['url'] ?? '';
            updateHyperlink($pdo, $id, $name, $url);
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
        $links = readAllHyperlinks($pdo);
        displayHyperlinkList($links);
}

// CRUD Functions

function createHyperlink($pdo, $name, $url)
{
    $stmt = $pdo->prepare("INSERT INTO hyperlinks (name, url) VALUES (:name, :url)");
    $stmt->execute([':name' => $name, ':url' => $url]);
    echo "Hyperlink created successfully!";
}

function readHyperlink($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT * FROM hyperlinks WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function readAllHyperlinks($pdo)
{
    $stmt = $pdo->query("SELECT * FROM hyperlinks");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateHyperlink($pdo, $id, $name, $url)
{
    $stmt = $pdo->prepare("UPDATE hyperlinks SET name = :name, url = :url WHERE id = :id");
    $stmt->execute([':name' => $name, ':url' => $url, ':id' => $id]);
    echo "Hyperlink updated successfully!";
}

function deleteHyperlink($pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM hyperlinks WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo "Hyperlink deleted successfully!";
}

// HTML Display Functions

function displayHyperlinkList($links)
{
    echo "<h1>Hyperlinks List</h1>";
    echo "<a href='?action=create'>Create New Hyperlink</a>";
    echo "<ul>";
    foreach ($links as $link) {
        echo "<li>" . htmlspecialchars($link['webgroup']) . " - <a href='" . htmlspecialchars($link['webdescription']) . "'>" . htmlspecialchars($link['website']) . "</a>";
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
    echo "<p>Name: " . htmlspecialchars($link['name']) . "</p>";
    echo "<p>URL: <a href='" . htmlspecialchars($link['url']) . "'>" . htmlspecialchars($link['url']) . "</a></p>";
    echo "<a href='?'>Back to list</a>";
}

function displayCreateForm()
{
    echo "<h1>Create Hyperlink</h1>";
    echo "<form method='post'>
            <label>Name: <input type='text' name='name'></label><br>
            <label>URL: <input type='url' name='url'></label><br>
            <input type='submit' value='Create'>
          </form>";
}

function displayUpdateForm($link)
{
    echo "<h1>Update Hyperlink</h1>";
    echo "<form method='post'>
            <label>Name: <input type='text' name='name' value='" . htmlspecialchars($link['name']) . "'></label><br>
            <label>URL: <input type='url' name='url' value='" . htmlspecialchars($link['url']) . "'></label><br>
            <input type='submit' value='Update'>
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