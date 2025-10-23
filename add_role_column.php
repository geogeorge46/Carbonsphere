<?php
// Quick fix to add the role column
require_once 'Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "<h2>Adding Role Column to Users Table</h2>";

    // Check if role column exists
    $check_query = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'Carbonsphere' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'";
    $stmt = $db->prepare($check_query);
    $stmt->execute();
    $exists = $stmt->fetchColumn() > 0;

    if (!$exists) {
        // Add the role column
        $stmt = $db->prepare("ALTER TABLE users ADD COLUMN role ENUM('user', 'seller', 'admin') DEFAULT 'user'");
        $stmt->execute();
        echo "<p style='color: green; font-weight: bold;'>✓ Role column added successfully!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Role column already exists.</p>";
    }

    echo "<h3>Ready to Register!</h3>";
    echo "<p><a href='register.php' style='background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Registration</a></p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Make sure your database exists and XAMPP is running.</p>";
}
?>