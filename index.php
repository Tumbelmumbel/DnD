<?php
/**
 * index.php
 * Example: Connect to IONOS MySQL database and fetch data securely
 */

// ==== 1. Database credentials (replace with your IONOS values) ====
$db_host = "db5020006586.hosting-data.io"; // Your IONOS MySQL host
$db_user = "dbu980466";                     // Your IONOS MySQL username
$db_pass = "100%GreatWhite";                 // Your IONOS MySQL password
$db_name = "dbs15432866";                     // Your IONOS MySQL database name

// ==== 2. Create connection ====
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// ==== 3. Check connection ====
if ($mysqli->connect_error) {
    die("Connection failed: " . htmlspecialchars($mysqli->connect_error));
}

/*// ==== 4. Example query (safe) ====
try {
    // Create a sample table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS sample_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
    ";
    if (!$mysqli->query($createTableSQL)) {
        throw new Exception("Table creation failed: " . $mysqli->error);
    }

    // Insert a sample row
    $stmt = $mysqli->prepare("INSERT INTO sample_data (name) VALUES (?)");
    $sampleName = "Test Entry";
    $stmt->bind_param("s", $sampleName);
    $stmt->execute();
    $stmt->close();

    // Fetch and display rows
    $result = $mysqli->query("SELECT id, name, created_at FROM sample_data ORDER BY id DESC");
    echo "<h1>Database Connection Successful</h1>";
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Name</th><th>Created At</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . htmlspecialchars($row['id']) . "</td><td>" .
             htmlspecialchars($row['name']) . "</td><td>" .
             htmlspecialchars($row['created_at']) . "</td></tr>";
    }
    echo "</table>";
    $result->free();

} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
 */
// ==== 5. Close connection ====
$mysqli->close();
?>
