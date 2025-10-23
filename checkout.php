<?php
require_once 'session_check.php';
requireAuth();

require_once 'Database.php';
require_once 'CartClass.php';
require_once 'Product.php';
require_once 'User.php';

$database = new Database();
$db = $database->getConnection();

$cart = new Cart($db);
$product = new Product($db);
$user = new User($db);

// Get cart items
$cartItems = $cart->getCartItems($_SESSION['user_id']);
$cartCount = $cart->getCartCount($_SESSION['user_id']);

// Get user details
$user->id = $_SESSION['user_id'];
$user->readOne();

// Calculate totals
$totalItems = 0;
$subtotal = 0;
$items = [];

while ($row = $cartItems->fetch(PDO::FETCH_ASSOC)) {
    $items[] = $row;
    $totalItems += $row['quantity'];
    $subtotal += $row['price'] * $row['quantity'];
}

$shipping = 0; // Free shipping
$tax = $subtotal * 0.08; // 8% tax
$total = $subtotal + $shipping + $tax;

// Redirect if cart is empty
if (empty($items)) {
    header('Location: cart.php');
    exit;
}

// Razorpay configuration
require_once 'config.php';
require_once 'PaymentController.php';
$paymentController = new PaymentController($db);

// Create Razorpay order
$razorpayOrder = $paymentController->createOrder($total);

if (!$razorpayOrder || isset($razorpayOrder['error'])) {
    $errorMsg = isset($razorpayOrder['error']) ? $razorpayOrder['error'] : 'Unknown error';
    die('Error creating payment order: ' . $errorMsg);
}

$razorpayKeyId = RAZORPAY_KEY_ID;
$orderId = $razorpayOrder['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Carbonsphere</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Razorpay Checkout Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        /* Navigation Styles */
        .main-nav {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 30px;
        }

        .nav-menu li a {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-menu li a:hover {
            color: #27ae60;
        }

        .nav-auth {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-nav {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-nav-login {
            background: #27ae60;
            color: white;
        }

        .btn-nav-login:hover {
            background: #229954;
        }

        .cart-icon {
            position: relative;
            color: #2c3e50;
            text-decoration: none;
            font-size: 20px;
            transition: color 0.3s ease;
        }

        .cart-icon:hover {
            color: #27ae60;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        /* Checkout Page Styles */
        .checkout-header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
            margin-bottom: 40px;
        }

        .checkout-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f8f9fa;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #27ae60;
        }

        .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
            min-height: 80px;
            resize: vertical;
            transition: border-color 0.3s ease;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #27ae60;
        }

        .order-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .summary-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .summary-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 15px;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .item-details {
            font-size: 14px;
            color: #666;
        }

        .summary-divider {
            border-top: 1px solid #eee;
            margin: 15px 0;
        }

        .summary-total {
            border-top: 2px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 20px;
            font-weight: 600;
            color: #27ae60;
        }

        .payment-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f8f9fa;
        }

        .payment-methods {
            margin-bottom: 20px;
        }

        .payment-method {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .payment-radio {
            margin-right: 10px;
        }

        .payment-label {
            font-weight: 500;
            color: #2c3e50;
        }

        .razorpay-btn {
            width: 100%;
            padding: 15px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .razorpay-btn:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }

        .razorpay-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 15px;
            font-size: 14px;
            color: #666;
        }

        .secure-badge i {
            color: #27ae60;
            margin-right: 8px;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #27ae60;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            max-width: 300px;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.error {
            background: #e74c3c;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <a href="index.php" class="logo">Carbonsphere</a>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="cart.php">Cart</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php endif; ?>
            </ul>
            <div class="nav-auth">
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo $cartCount; ?></span>
                </a>
                <a href="logout.php" class="btn-nav btn-nav-login">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="checkout-header">
        <div class="container">
            <h1>Checkout</h1>
            <p>Complete your order securely</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="checkout-container">
        <div class="checkout-grid">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <div class="form-section">
                    <h2 class="section-title">Billing Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($user->first_name); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($user->last_name); ?>" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" value="<?php echo htmlspecialchars($user->email); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-input" value="<?php echo htmlspecialchars($user->phone); ?>" readonly>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="section-title">Shipping Address</h2>
                    <div class="form-group full-width">
                        <label class="form-label">Address Line 1</label>
                        <input type="text" class="form-input" id="address1" placeholder="Street address, P.O. box" required>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" class="form-input" id="address2" placeholder="Apartment, suite, unit, building, floor, etc.">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" class="form-input" id="city" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">State</label>
                            <input type="text" class="form-input" id="state" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ZIP Code</label>
                            <input type="text" class="form-input" id="zipcode" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-input" value="India" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="section-title">Order Notes (Optional)</h2>
                    <div class="form-group full-width">
                        <label class="form-label">Special instructions</label>
                        <textarea class="form-textarea" id="orderNotes" placeholder="Any special delivery instructions..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-title">Order Summary</div>

                <?php foreach ($items as $item): ?>
                    <div class="summary-item">
                        <div style="display: flex; align-items: center;">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php endif; ?>
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-details">Qty: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?></div>
                            </div>
                        </div>
                        <div>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                <?php endforeach; ?>

                <div class="summary-divider"></div>

                <div class="summary-item">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </div>

                <div class="summary-item">
                    <span>Shipping</span>
                    <span><?php echo $shipping == 0 ? 'Free' : '$' . number_format($shipping, 2); ?></span>
                </div>

                <div class="summary-item">
                    <span>Tax (8%)</span>
                    <span>$<?php echo number_format($tax, 2); ?></span>
                </div>

                <div class="summary-item summary-total">
                    <span>Total</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>

                <div class="payment-section">
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" id="razorpay" name="payment_method" class="payment-radio" checked>
                            <label for="razorpay" class="payment-label">Pay with Razorpay (Credit/Debit Card, UPI, Net Banking)</label>
                        </div>
                    </div>

                    <button class="razorpay-btn" onclick="initiatePayment()">
                        <i class="fas fa-credit-card"></i> Pay $<?php echo number_format($total, 2); ?> Securely
                    </button>

                    <div class="secure-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>SSL Encrypted & Secure Payment</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script src="product.js"></script>
    <script>
        // Razorpay payment configuration
        const razorpayKey = '<?php echo $razorpayKeyId; ?>';
        const orderId = '<?php echo $orderId; ?>';
        const amount = <?php echo $total * 100; ?>; // Amount in paisa
        const currency = 'INR';

        // Form validation
        function validateForm() {
            const requiredFields = ['address1', 'city', 'state', 'zipcode'];
            let isValid = true;

            requiredFields.forEach(field => {
                const element = document.getElementById(field);
                if (!element.value.trim()) {
                    element.style.borderColor = '#e74c3c';
                    isValid = false;
                } else {
                    element.style.borderColor = '#e1e8ed';
                }
            });

            return isValid;
        }

        // Initiate Razorpay payment
        function initiatePayment() {
            if (!validateForm()) {
                showToast('Please fill in all required fields', 'error');
                return;
            }

            // Get form data
            const billingData = {
                firstName: '<?php echo addslashes($user->first_name); ?>',
                lastName: '<?php echo addslashes($user->last_name); ?>',
                email: '<?php echo $user->email; ?>',
                phone: '<?php echo $user->phone; ?>',
                address1: document.getElementById('address1').value,
                address2: document.getElementById('address2').value,
                city: document.getElementById('city').value,
                state: document.getElementById('state').value,
                zipcode: document.getElementById('zipcode').value,
                orderNotes: document.getElementById('orderNotes').value
            };

            // Razorpay options
            const options = {
                key: razorpayKey,
                amount: amount,
                currency: currency,
                name: 'Carbonsphere',
                description: 'Eco-friendly Products Purchase',
                order_id: orderId,
                prefill: {
                    name: billingData.firstName + ' ' + billingData.lastName,
                    email: billingData.email,
                    contact: billingData.phone
                },
                notes: {
                    address: billingData.address1 + ', ' + billingData.city + ', ' + billingData.state + ' ' + billingData.zipcode
                },
                theme: {
                    color: '#27ae60'
                },
                handler: function (response) {
                    // Payment successful
                    processOrder(response, billingData);
                },
                modal: {
                    ondismiss: function() {
                        showToast('Payment cancelled', 'error');
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.open();
        }

        // Process order after successful payment
        async function processOrder(paymentResponse, billingData) {
            try {
                const orderData = {
                    payment_id: paymentResponse.razorpay_payment_id,
                    order_id: paymentResponse.razorpay_order_id,
                    signature: paymentResponse.razorpay_signature,
                    billing_data: billingData,
                    items: <?php echo json_encode($items); ?>,
                    totals: {
                        subtotal: <?php echo $subtotal; ?>,
                        shipping: <?php echo $shipping; ?>,
                        tax: <?php echo $tax; ?>,
                        total: <?php echo $total; ?>
                    }
                };

                const response = await fetch('process_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(orderData)
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Order placed successfully!');
                    // Redirect to order confirmation page
                    setTimeout(() => {
                        window.location.href = 'order_confirmation.php?order_ids=' + result.order_ids.join(',');
                    }, 2000);
                } else {
                    showToast('Failed to process order. Please contact support.', 'error');
                }
            } catch (error) {
                console.error('Error processing order:', error);
                showToast('Failed to process order. Please try again.', 'error');
            }
        }

        // Show cart (redirect to cart page)
        function showCart() {
            window.location.href = 'cart.php';
        }
    </script>
</body>
</html>