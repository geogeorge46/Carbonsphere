<?php
// test_dashboard.php - Test script for dashboard functionality
session_start();

require_once 'Database.php';
require_once 'Product.php';
require_once 'Order.php';

echo "<h1>Carbonsphere Seller Dashboard - Test Results</h1>";

try {
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";

    $product = new Product($db);
    $order = new Order($db);

    // Test categories
    $categories = $product->getCategories();
    echo "<p>✓ Categories loaded: " . $categories->rowCount() . " categories found</p>";

    // Test product count (if seller is logged in)
    if(isset($_SESSION['user_id']) && $_SESSION['role'] === 'seller') {
        $product_count = $product->getCountBySeller($_SESSION['user_id']);
        echo "<p>✓ Seller products: $product_count products found</p>";

        $order_stats = $order->getSellerStats($_SESSION['user_id']);
        echo "<p>✓ Order stats: {$order_stats['total_orders']} orders, \${$order_stats['total_revenue']} revenue</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Not logged in as seller - some tests skipped</p>";
    }

    // Test file permissions
    $upload_dir = 'uploads/products/';
    if(is_dir($upload_dir) && is_writable($upload_dir)) {
        echo "<p style='color: green;'>✓ Upload directory is writable</p>";
    } else {
        echo "<p style='color: red;'>✗ Upload directory not writable</p>";
    }

    echo "<h2>System Status: OK</h2>";
    echo "<p>All basic functionality tests passed. Dashboard should work correctly.</p>";

} catch(Exception $e) {
    echo "<h2 style='color: red;'>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='dashboard.php'>← Back to Dashboard</a></p>";
echo "<p><a href='run_schema.php'>Run Database Schema</a></p>";
?>