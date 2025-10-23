<?php
// get_product.php - API endpoint to get product data for editing
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a seller
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

require_once 'Database.php';
require_once 'Product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

$product->id = $_GET['id'];

// Verify the product belongs to the current seller
$query = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $product->id);
$stmt->bindParam(2, $_SESSION['user_id']);
$stmt->execute();

if($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode([
        'success' => true,
        'product' => [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'stock_quantity' => $row['stock_quantity'],
            'category' => $row['category'],
            'status' => $row['status'],
            'image' => $row['image']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found or access denied']);
}
?>