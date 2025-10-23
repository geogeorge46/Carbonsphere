<?php
// Check which columns exist in the users table
require_once 'Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "<h2>Checking Existing Columns in Users Table</h2>";

    $columns_to_check = ['user_id', 'first_name', 'last_name', 'phone', 'role'];

    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th>Column Name</th><th>Status</th><th>Action Needed</th></tr>";

    foreach ($columns_to_check as $column) {
        $query = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = 'Carbonsphere' AND TABLE_NAME = 'users' AND COLUMN_NAME = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$column]);
        $exists = $stmt->fetchColumn() > 0;

        echo "<tr>";
        echo "<td><strong>$column</strong></td>";
        echo "<td style='color: " . ($exists ? "green" : "red") . ";'>" . ($exists ? "EXISTS" : "MISSING") . "</td>";
        echo "<td>" . ($exists ? "No action needed" : "Will be added") . "</td>";
        echo "</tr>";
    }

    echo "</table>";

    echo "<h3>Recommended Action:</h3>";
    echo "<p><strong>Click here to run the migration:</strong> <a href='run_migration.php' style='background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Migration</a></p>";

    echo "<p><em>The PHP migration script will safely add only the missing columns.</em></p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Database Connection Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Make sure your database 'Carbonsphere' exists and XAMPP is running.</p>";
}
?>