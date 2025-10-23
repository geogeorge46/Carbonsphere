<?php
session_start();
require_once 'Database.php';
require_once 'session_check.php';
require_once 'config.php';
require_once 'CartClass.php';
require_once 'PaymentController.php';
require_once 'User.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);
$user = new User($db);

// Get user details
$user->id = $_SESSION['user_id'];
$user->readOne();

$userId = $_SESSION['user_id'];
$cartItems = $cart->getCartItems($userId);
$cartTotal = $cart->getCartTotal($userId);

// Calculate tax (optional - 18% GST)
$taxRate = 0.18;
$taxAmount = $cartTotal * $taxRate;
$totalPayable = $cartTotal + $taxAmount;

// Create Razorpay order
$paymentController = new PaymentController($db);
$razorpayOrder = $paymentController->createOrder($totalPayable);

if (!$razorpayOrder) {
    die('Error creating payment order. Please try again.');
}

$orderId = $razorpayOrder['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Carbonsphere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="payment.css" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="payment-container">
                    <div class="payment-header">
                        <h1><i class="fas fa-credit-card"></i> Secure Payment</h1>
                        <p>Complete your purchase securely</p>
                    </div>

                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-items">
                            <?php
                            $cartItems->execute(); // Reset cursor
                            while ($item = $cartItems->fetch(PDO::FETCH_ASSOC)):
                            ?>
                                <div class="summary-item">
                                    <div class="item-details">
                                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <span class="item-quantity">Qty: <?php echo $item['quantity']; ?></span>
                                    </div>
                                    <span class="item-price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <div class="summary-totals">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>₹<?php echo number_format($cartTotal, 2); ?></span>
                            </div>
                            <div class="total-row">
                                <span>Tax (GST 18%):</span>
                                <span>₹<?php echo number_format($taxAmount, 2); ?></span>
                            </div>
                            <div class="total-row total-payable">
                                <span><strong>Total Payable:</strong></span>
                                <span><strong>₹<?php echo number_format($totalPayable, 2); ?></strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="payment-section">
                        <h3>Payment Details</h3>
                        <div class="payment-info">
                            <div class="info-row">
                                <span>Order ID:</span>
                                <span><?php echo htmlspecialchars($orderId); ?></span>
                            </div>
                            <div class="info-row">
                                <span>Amount:</span>
                                <span>₹<?php echo number_format($totalPayable, 2); ?></span>
                            </div>
                            <div class="info-row">
                                <span>Currency:</span>
                                <span>INR</span>
                            </div>
                        </div>

                        <div class="payment-actions">
                            <button id="pay-btn" class="btn btn-success btn-lg">
                                <i class="fas fa-lock"></i> Pay Now ₹<?php echo number_format($totalPayable, 2); ?>
                            </button>
                            <a href="cart.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Processing...</span>
                    </div>
                    <p class="mt-3">Processing your payment...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('pay-btn').onclick = function(e) {
        e.preventDefault();

        // Show loading modal
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();

        const options = {
            key: '<?php echo RAZORPAY_KEY_ID; ?>', // Test key
            amount: <?php echo $totalPayable * 100; ?>, // Amount in paisa
            currency: 'INR',
            name: 'Carbonsphere',
            description: 'Eco-friendly Products Purchase',
            image: 'https://example.com/logo.png', // Replace with your logo URL
            order_id: '<?php echo $orderId; ?>',
            prefill: {
                name: '<?php echo htmlspecialchars($user->first_name . ' ' . $user->last_name); ?>',
                email: '<?php echo htmlspecialchars($user->email); ?>',
                contact: '<?php echo htmlspecialchars($user->phone); ?>'
            },
            theme: {
                color: '#3498db'
            },
            handler: function(response) {
                // Handle success
                loadingModal.hide();

                // Send payment details to server for verification
                fetch('process_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'order_success.php?order_id=' + response.razorpay_order_id;
                    } else {
                        alert('Payment verification failed: ' + data.message);
                        window.location.href = 'cart.php';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing your payment.');
                    window.location.href = 'cart.php';
                });
            },
            modal: {
                ondismiss: function() {
                    loadingModal.hide();
                }
            }
        };

        const rzp = new Razorpay(options);
        rzp.open();
    };
    </script>
</body>
</html>