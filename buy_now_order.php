<?php
session_start();
require_once 'Database.php';
require_once 'session_check.php';
require_once 'Product.php';
require_once 'PaymentController.php';
require_once 'User.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();
    $product = new Product($db);
    $user = new User($db);

    // Get user details
    $user->id = $_SESSION['user_id'];
    $user->readOne();

    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($productId <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        exit();
    }

    // Load product
    $product->id = $productId;
    if (!$product->readOne()) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }

    // Check stock
    if ($product->stock_quantity < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
        exit();
    }

    // Calculate total (with tax)
    $subtotal = $product->price * $quantity;
    $taxRate = 0.18; // 18% GST
    $taxAmount = $subtotal * $taxRate;
    $totalPayable = $subtotal + $taxAmount;

    // Create Razorpay order
    $paymentController = new PaymentController($db);
    $razorpayOrder = $paymentController->createOrder($totalPayable);

    if (!$razorpayOrder || isset($razorpayOrder['error'])) {
        $errorMsg = isset($razorpayOrder['error']) ? $razorpayOrder['error'] : 'Unknown error';
        echo json_encode(['success' => false, 'message' => 'Error creating payment order: ' . $errorMsg]);
        exit();
    }

    echo json_encode([
        'success' => true,
        'order_id' => $razorpayOrder['id'],
        'amount' => $totalPayable * 100, // in paisa
        'product' => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'tax' => $taxAmount,
            'total' => $totalPayable
        ]
    ]);
} catch (Exception $e) {
    error_log('Buy now order error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>