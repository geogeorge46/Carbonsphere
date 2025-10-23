<?php
session_start();
require_once 'Database.php';
require_once 'session_check.php';
require_once 'PaymentController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to process payment.']);
    exit();
}

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$paymentController = new PaymentController($db);

$userId = $_SESSION['user_id'];

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid input data');
    }

    $razorpayOrderId = $input['razorpay_order_id'] ?? '';
    $razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
    $razorpaySignature = $input['razorpay_signature'] ?? '';

    if (empty($razorpayOrderId) || empty($razorpayPaymentId) || empty($razorpaySignature)) {
        throw new Exception('Missing payment details');
    }

    // Process payment success
    $result = $paymentController->processPaymentSuccess($userId, $razorpayOrderId, $razorpayPaymentId, $razorpaySignature);

    echo json_encode($result);

} catch (Exception $e) {
    error_log('Payment processing error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your payment. Please contact support.'
    ]);
}
?>