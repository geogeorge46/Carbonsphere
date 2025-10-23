<?php
session_start();
require_once 'Database.php';
require_once 'session_check.php';
require_once 'PaymentController.php';
require_once 'Order.php';

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit();
    }

    $razorpayOrderId = $data['razorpay_order_id'] ?? '';
    $razorpayPaymentId = $data['razorpay_payment_id'] ?? '';
    $razorpaySignature = $data['razorpay_signature'] ?? '';
    $productId = $data['product_id'] ?? 0;
    $quantity = $data['quantity'] ?? 0;

    if (!$razorpayOrderId || !$razorpayPaymentId || !$razorpaySignature || !$productId || !$quantity) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();
    $paymentController = new PaymentController($db);

    // Verify payment
    if (!$paymentController->verifyPayment($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)) {
        echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
        exit();
    }

    // Get product details
    $productQuery = "SELECT price, seller_id FROM products WHERE id = ?";
    $productStmt = $db->prepare($productQuery);
    $productStmt->bindParam(1, $productId);
    $productStmt->execute();

    if ($productStmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }

    $product = $productStmt->fetch(PDO::FETCH_ASSOC);

    // Begin transaction
    $db->beginTransaction();

    $order = new Order($db);
    $order->buyer_id = $_SESSION['user_id'];
    $order->seller_id = $product['seller_id'];
    $order->product_id = $productId;
    $order->quantity = $quantity;
    $order->unit_price = $product['price'];
    $order->total_price = $product['price'] * $quantity;
    $order->status = 'paid';
    $order->shipping_address = ''; // Can be updated later
    $order->notes = 'Paid via Razorpay - Payment ID: ' . $razorpayPaymentId;

    if (!$order->create()) {
        throw new Exception('Failed to create order');
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Payment processed successfully', 'order_id' => $razorpayOrderId]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Buy now payment processing failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Payment processing failed: ' . $e->getMessage()]);
}
?>