<?php
require_once 'session_check.php';

if (!validateSession()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

require_once 'Database.php';
require_once 'CartClass.php';
require_once 'Product.php';
require_once 'PaymentController.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$cart = new Cart($db);
$product = new Product($db);
$paymentController = new PaymentController($db);

// Verify Razorpay payment
if (!$paymentController->verifyPayment($data['order_id'], $data['payment_id'], $data['signature'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
    exit;
}

try {
    $db->beginTransaction();

    // Insert order
    $query = "INSERT INTO orders SET
              order_id = ?,
              buyer_id = ?,
              seller_id = ?,
              product_id = ?,
              quantity = ?,
              unit_price = ?,
              total_price = ?,
              status = 'paid',
              shipping_address = ?,
              notes = ?";

    $stmt = $db->prepare($query);

    $shippingAddress = $data['billing_data']['address1'];
    if (!empty($data['billing_data']['address2'])) {
        $shippingAddress .= ', ' . $data['billing_data']['address2'];
    }
    $shippingAddress .= ', ' . $data['billing_data']['city'] . ', ' . $data['billing_data']['state'] . ' ' . $data['billing_data']['zipcode'];

    $notes = $data['billing_data']['orderNotes'] ?? '';
    $notes .= ' - Payment ID: ' . $data['payment_id'];

    $orderIds = [];

    // Process each item
    foreach ($data['items'] as $item) {
        // Get seller ID for the product
        $product->id = $item['product_id'];
        $product->readOne();

        // Generate unique order ID for each item
        $orderId = generateOrderId();

        $stmt->execute([
            $orderId,
            $_SESSION['user_id'],
            $product->seller_id,
            $item['product_id'],
            $item['quantity'],
            $item['price'],
            $item['price'] * $item['quantity'],
            $shippingAddress,
            $notes
        ]);

        $orderIds[] = $orderId;

        // Update product stock
        $updateStockQuery = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
        $updateStmt = $db->prepare($updateStockQuery);
        $updateStmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Clear user's cart
    $cart->clearCart($_SESSION['user_id']);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order processed successfully',
        'order_ids' => $orderIds
    ]);

} catch (Exception $e) {
    $db->rollBack();
    error_log('Order processing error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to process order']);
}



// Generate unique order ID
function generateOrderId() {
    return 'ORD' . date('YmdHis') . rand(1000, 9999);
}
?>