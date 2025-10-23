<?php
require_once 'config.php';
require_once 'CartClass.php';
require_once 'Order.php';

class PaymentController {
    private $keyId;
    private $keySecret;
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
        // Razorpay test credentials
        $this->keyId = RAZORPAY_KEY_ID;
        $this->keySecret = RAZORPAY_KEY_SECRET;
    }

    // Create Razorpay order using curl
    public function createOrder($amount, $currency = 'INR', $receipt = null) {
        $url = 'https://api.razorpay.com/v1/orders';
        $data = [
            'amount' => $amount * 100, // Amount in paisa
            'currency' => $currency,
            'receipt' => $receipt ?: 'rcpt_' . time(),
            'payment_capture' => 1
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($this->keyId . ':' . $this->keySecret)
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing, remove in production

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            return json_decode($response, true);
        } else {
            error_log('Razorpay order creation failed: HTTP ' . $httpCode . ' - ' . $response);
            return ['error' => $response, 'http_code' => $httpCode];
        }
    }

    // Verify payment signature manually
    public function verifyPayment($orderId, $paymentId, $signature) {
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($expectedSignature, $signature);
    }

    // Process successful payment
    public function processPaymentSuccess($userId, $razorpayOrderId, $razorpayPaymentId, $signature) {
        // Verify payment
        if (!$this->verifyPayment($razorpayOrderId, $razorpayPaymentId, $signature)) {
            return ['success' => false, 'message' => 'Payment verification failed'];
        }

        // Get cart items
        $cart = new Cart($this->conn);
        $cartItems = $cart->getCartItems($userId);

        if ($cartItems->rowCount() == 0) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }

        // Begin transaction
        $this->conn->beginTransaction();

        try {
            $items = $cartItems->fetchAll(PDO::FETCH_ASSOC);

            // Create orders for each cart item
            foreach ($items as $item) {
                $order = new Order($this->conn);
                $order->buyer_id = $userId;
                $order->seller_id = $item['seller_id']; // Assuming products have seller_id
                $order->product_id = $item['id'];
                $order->quantity = $item['quantity'];
                $order->unit_price = $item['price'];
                $order->total_price = $item['price'] * $item['quantity'];
                $order->status = 'paid';
                $order->shipping_address = ''; // Will be filled from form
                $order->notes = 'Paid via Razorpay - Payment ID: ' . $razorpayPaymentId;

                if (!$order->create()) {
                    throw new Exception('Failed to create order');
                }
            }

            // Clear cart
            $cart->clearCart($userId);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Payment processed successfully'];

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('Payment processing failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment processing failed'];
        }
    }
}
?>