<?php
session_start();
require_once 'Database.php';
require_once 'Product.php';
require_once 'CartClass.php';
require_once 'User.php';
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$cart = new Cart($db);
$user = new User($db);

// Get user details if logged in
if (isset($_SESSION['user_id'])) {
    $user->id = $_SESSION['user_id'];
    $user->readOne();
}

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    header('Location: products.php');
    exit;
}

// Load product details
$product->id = $productId;
if (!$product->readOne()) {
    header('Location: products.php');
    exit;
}

// Get cart count if user is logged in
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $cartCount = $cart->getCartItemCount($_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product->name); ?> - Carbonsphere</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        .btn-nav-signup {
            background: transparent;
            color: #27ae60;
            border: 2px solid #27ae60;
        }

        .btn-nav-signup:hover {
            background: #27ae60;
            color: white;
        }

        /* Product Detail Styles */
        .product-detail-header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 40px 0;
            text-align: center;
        }

        .product-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .product-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-bottom: 50px;
        }

        .product-images {
            position: sticky;
            top: 20px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            background: #f8f9fa;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .main-image .no-image {
            color: #666;
            font-size: 64px;
        }

        .thumbnail-images {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s ease;
        }

        .thumbnail:hover,
        .thumbnail.active {
            border-color: #27ae60;
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 6px;
        }

        .product-info h1 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .product-price {
            font-size: 28px;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 20px;
        }

        .product-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 25px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
        }

        .meta-item i {
            color: #27ae60;
        }

        .product-description {
            color: #555;
            line-height: 1.8;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            border-color: #27ae60;
            color: #27ae60;
        }

        .quantity-input {
            width: 80px;
            height: 40px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
        }

        .quantity-input:focus {
            outline: none;
            border-color: #27ae60;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .btn-primary {
            flex: 1;
            padding: 15px 30px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }

        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            flex: 1;
            padding: 15px 30px;
            background: transparent;
            color: #27ae60;
            border: 2px solid #27ae60;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #27ae60;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }

        .seller-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
        }

        .seller-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .seller-avatar {
            width: 60px;
            height: 60px;
            background: #27ae60;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .seller-details h3 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .seller-details p {
            color: #666;
            margin: 0;
        }

        .login-prompt {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 25px;
        }

        .login-prompt a {
            color: #27ae60;
            font-weight: 500;
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

        /* Navigation with cart */
        .nav-cart {
            position: relative;
            margin-left: 20px;
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

        /* Cart Modal */
        .cart-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: flex-end;
            align-items: flex-start;
        }

        .cart-modal.show {
            display: flex;
        }

        .cart-modal-content {
            background: white;
            width: 400px;
            max-width: 90vw;
            height: 100vh;
            box-shadow: -5px 0 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        .cart-modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-modal-header h3 {
            margin: 0;
            color: #2c3e50;
        }

        .cart-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-close:hover {
            color: #e74c3c;
        }

        .cart-modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 0;
        }

        .cart-loading {
            padding: 40px;
            text-align: center;
            color: #666;
        }

        .cart-empty {
            padding: 40px 20px;
            text-align: center;
        }

        .cart-empty i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }

        .cart-empty p {
            color: #666;
            margin-bottom: 20px;
        }

        .cart-item-modal {
            display: flex;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .cart-item-modal:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-right: 15px;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .cart-item-price {
            color: #27ae60;
            font-size: 14px;
            font-weight: 500;
        }

        .cart-item-remove {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 8px;
            cursor: pointer;
            font-size: 12px;
        }

        .cart-item-remove:hover {
            background: #c0392b;
        }

        .cart-footer {
            border-top: 1px solid #eee;
            padding: 20px;
        }

        .cart-total {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: 600;
            color: #27ae60;
            margin-bottom: 15px;
        }

        .cart-checkout-btn {
            width: 100%;
            padding: 12px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background 0.3s ease;
        }

        .cart-checkout-btn:hover {
            background: #229954;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .product-detail-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .product-images {
                position: static;
            }

            .main-image {
                height: 300px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .quantity-selector {
                justify-content: center;
            }

            .cart-modal-content {
                width: 100%;
                max-width: 100vw;
            }
        }

        @media (max-width: 480px) {
            .product-detail-container {
                padding: 20px 15px;
            }

            .main-image {
                height: 250px;
            }

            .product-info h1 {
                font-size: 24px;
            }

            .product-price {
                font-size: 24px;
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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] === 'seller'): ?>
                        <li><a href="dashboard.php">Seller Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="user_dashboard.php">My Dashboard</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <div class="nav-auth">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="cart.php" class="cart-icon nav-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cartCount"><?php echo $cartCount; ?></span>
                    </a>
                    <a href="logout.php" class="btn-nav btn-nav-login">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn-nav btn-nav-login">Login</a>
                    <a href="register.php" class="btn-nav btn-nav-signup">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="product-detail-header">
        <div class="product-detail-container">
            <h1>Product Details</h1>
        </div>
    </section>

    <!-- Main Content -->
    <div class="product-detail-container">
        <div class="product-detail-grid">
            <!-- Product Images -->
            <div class="product-images">
                <div class="main-image">
                    <?php if($product->image): ?>
                        <img src="<?php echo htmlspecialchars($product->image); ?>"
                             alt="<?php echo htmlspecialchars($product->name); ?>"
                             id="mainImage">
                    <?php else: ?>
                        <div class="no-image"><i class="fas fa-image"></i></div>
                    <?php endif; ?>
                </div>

                <!-- Thumbnail images would go here if we had multiple images -->
                <div class="thumbnail-images">
                    <?php if($product->image): ?>
                        <div class="thumbnail active">
                            <img src="<?php echo htmlspecialchars($product->image); ?>"
                                 alt="Thumbnail 1"
                                 onclick="changeImage(this.src)">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Information -->
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product->name); ?></h1>
                <div class="product-price">$<?php echo number_format($product->price, 2); ?></div>

                <div class="product-meta">
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span><?php echo htmlspecialchars($product->category); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-boxes"></i>
                        <span><?php echo $product->stock_quantity; ?> in stock</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo date('M j, Y', strtotime($product->created_at)); ?></span>
                    </div>
                </div>

                <div class="product-description">
                    <?php echo nl2br(htmlspecialchars($product->description)); ?>
                </div>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="quantity-selector">
                        <button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                        <input type="number" class="quantity-input" id="quantity" value="1" min="1" max="<?php echo $product->stock_quantity; ?>">
                        <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                    </div>

                    <div class="action-buttons">
                        <button class="btn-primary"
                                onclick="addToCart(<?php echo $product->id; ?>, '<?php echo htmlspecialchars(addslashes($product->name)); ?>')"
                                <?php echo $product->stock_quantity <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-cart-plus"></i>
                            <?php echo $product->stock_quantity <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                        </button>

                        <a href="#" class="btn-secondary" onclick="buyNow()">
                            <i class="fas fa-credit-card"></i>
                            Buy Now
                        </a>
                    </div>
                <?php else: ?>
                    <div class="login-prompt">
                        <p><i class="fas fa-lock"></i> Please <a href="login.php">login</a> to purchase this product</p>
                    </div>

                    <div class="action-buttons">
                        <a href="login.php" class="btn-primary">
                            <i class="fas fa-sign-in-alt"></i>
                            Login to Buy
                        </a>

                        <a href="register.php" class="btn-secondary">
                            <i class="fas fa-user-plus"></i>
                            Create Account
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Seller Information -->
        <div class="seller-info">
            <div class="seller-header">
                <div class="seller-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="seller-details">
                    <h3>Seller Information</h3>
                    <p>Sold by verified eco-friendly seller</p>
                </div>
            </div>
            <p>This product is sold by one of our verified sellers who are committed to sustainable practices and environmental responsibility.</p>
        </div>
    </div>

    <!-- Cart Modal -->
    <div id="cartModal" class="cart-modal">
        <div class="cart-modal-content">
            <div class="cart-modal-header">
                <h3>Shopping Cart</h3>
                <button class="cart-close" onclick="closeCartModal()">&times;</button>
            </div>
            <div class="cart-modal-body" id="cartModalBody">
                <div class="cart-loading">Loading cart...</div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script src="product.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        // Change main image
        function changeImage(src) {
            document.getElementById('mainImage').src = src;

            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            event.target.closest('.thumbnail').classList.add('active');
        }

        // Quantity controls
        function changeQuantity(delta) {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value);
            const newValue = currentValue + delta;
            const maxStock = <?php echo $product->stock_quantity; ?>;

            if (newValue >= 1 && newValue <= maxStock) {
                input.value = newValue;
            }
        }

        // Buy now functionality
        async function buyNow() {
            try {
                const quantity = document.getElementById('quantity') ?
                    parseInt(document.getElementById('quantity').value) : 1;

                const formData = new FormData();
                formData.append('product_id', <?php echo $productId; ?>);
                formData.append('quantity', quantity);

                const response = await fetch('buy_now_order.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Open Razorpay checkout
                    const options = {
                        key: '<?php echo RAZORPAY_KEY_ID; ?>', // Test key
                        amount: result.amount,
                        currency: 'INR',
                        name: 'Carbonsphere',
                        description: 'Purchase: <?php echo htmlspecialchars(addslashes($product->name)); ?>',
                        order_id: result.order_id,
                        prefill: {
                            name: '<?php echo htmlspecialchars(addslashes($user->first_name ?? '' . ' ' . $user->last_name ?? '')); ?>',
                            email: '<?php echo htmlspecialchars($user->email ?? ''); ?>',
                            contact: '<?php echo htmlspecialchars($user->phone ?? ''); ?>'
                        },
                        theme: {
                            color: '#27ae60'
                        },
                        handler: function(response) {
                            // Handle success
                            processBuyNowPayment(response, result.product);
                        },
                        modal: {
                            ondismiss: function() {
                                showToast('Payment cancelled', 'error');
                            }
                        }
                    };

                    const rzp = new Razorpay(options);
                    rzp.open();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error with buy now:', error);
                showToast('Failed to process purchase. Please try again.', 'error');
            }
        }

        // Process buy now payment
        async function processBuyNowPayment(razorpayResponse, product) {
            try {
                const response = await fetch('process_buy_now_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        razorpay_order_id: razorpayResponse.razorpay_order_id,
                        razorpay_payment_id: razorpayResponse.razorpay_payment_id,
                        razorpay_signature: razorpayResponse.razorpay_signature,
                        product_id: product.id,
                        quantity: product.quantity
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Payment successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'order_success.php?order_id=' + razorpayResponse.razorpay_order_id;
                    }, 1000);
                } else {
                    showToast('Payment verification failed: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                showToast('An error occurred while processing your payment.', 'error');
            }
        }

        // Show cart modal
        function showCart() {
            const modal = document.getElementById('cartModal');
            const modalBody = document.getElementById('cartModalBody');

            modalBody.innerHTML = '<div class="cart-loading">Loading cart...</div>';
            modal.classList.add('show');

            // Load cart items
            loadCartModal();
        }

        // Close cart modal
        function closeCartModal() {
            const modal = document.getElementById('cartModal');
            modal.classList.remove('show');
        }

        // Load cart items into modal
        async function loadCartModal() {
            try {
                const response = await fetch('CartController.php?action=get_items');
                const result = await response.json();

                const modalBody = document.getElementById('cartModalBody');

                if (result.success && result.items.length > 0) {
                    let html = '';

                    result.items.forEach(item => {
                        // Handle missing product data
                        item.name = item.name || 'Product not found';
                        item.price = item.price || 0;
                        item.image = item.image || null;

                        html += `
                            <div class="cart-item-modal">
                                <div class="cart-item-image">
                                    ${item.image ? `<img src="${item.image}" alt="${item.name}">` : '<i class="fas fa-image"></i>'}
                                </div>
                                <div class="cart-item-details">
                                    <div class="cart-item-name">${item.name}</div>
                                    <div class="cart-item-price">$${item.price} × ${item.quantity} = $${(item.price * item.quantity).toFixed(2)}</div>
                                </div>
                                <button class="cart-item-remove" onclick="removeFromCart(${item.cart_id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    });

                    // Calculate total
                    const total = result.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);

                    html += `
                        <div class="cart-footer">
                            <div class="cart-total">
                                <span>Total:</span>
                                <span>$${total.toFixed(2)}</span>
                            </div>
                            <a href="cart.php" class="cart-checkout-btn">View Cart & Checkout</a>
                        </div>
                    `;

                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = `
                        <div class="cart-empty">
                            <i class="fas fa-shopping-cart"></i>
                            <p>Your cart is empty</p>
                            <a href="products.php" class="cart-checkout-btn" style="margin-top: 15px;">Continue Shopping</a>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading cart:', error);
                const modalBody = document.getElementById('cartModalBody');
                modalBody.innerHTML = '<div class="cart-loading">Error loading cart</div>';
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('cartModal');
            if (event.target === modal) {
                closeCartModal();
            }
        });
    </script>
</body>
</html>