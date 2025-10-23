<?php
session_start();
require_once 'Database.php';
require_once 'CartClass.php';
require_once 'session_check.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require login
requireAuth();

$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);

$userId = (int)$_SESSION['user_id'];

// Get cart items
try {
    $stmt = $cart->getCartItems($userId);
    $cartItems = [];
    if ($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cartItems[] = $row;
        }
    } else {
        // Query failed, perhaps log error
        $cartItems = [];
        echo "<!-- Query failed -->";
    }
} catch (Exception $e) {
    echo "<!-- Error: " . $e->getMessage() . " -->";
    $cartItems = [];
}

// Get cart total
$cartTotal = $cart->getCartTotal($userId);
$cartCount = $cart->getCartCount($userId);

// Debug output
echo "<!-- User ID: $userId, Cart Count: $cartCount, Cart Items: " . count($cartItems) . " -->";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Carbonsphere</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .cart-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .cart-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .cart-items {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .cart-item-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .cart-item-price {
            color: #27ae60;
            font-weight: 600;
            font-size: 16px;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            margin: 0 20px;
        }

        .quantity-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 4px;
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 5px;
            margin: 0 10px;
        }

        .cart-item-total {
            font-weight: 600;
            color: #2c3e50;
            margin: 0 20px;
            min-width: 80px;
            text-align: right;
        }

        .cart-item-remove {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .cart-item-remove:hover {
            background: #c0392b;
        }

        .cart-summary {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .cart-summary h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-row.total {
            font-size: 20px;
            font-weight: 600;
            color: #27ae60;
            border-bottom: none;
            border-top: 2px solid #27ae60;
            margin-top: 20px;
            padding-top: 20px;
        }

        .cart-actions {
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            margin: 0 10px;
        }

        .btn-primary {
            background: #27ae60;
            color: white;
        }

        .btn-primary:hover {
            background: #229954;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .empty-cart i {
            font-size: 64px;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .empty-cart h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .empty-cart p {
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .cart-item-quantity {
                margin: 10px 0;
            }

            .cart-item-total {
                margin: 10px 0;
            }
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

        /* Navigation styles */
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

        .nav-logo a {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .nav-links a {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #27ae60;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php">Carbonsphere</a>
            </div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
                <a href="cart.php" class="cart-icon nav-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount"><?php echo $cartCount; ?></span>
                </a>
            </div>
        </div>
    </nav>

    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p><?php echo count($cartItems); ?> items in your cart</p>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Add some products to get started!</p>
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-cart-id="<?php echo $item['cart_id']; ?>">
                        <div class="cart-item-image">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name'] ?: 'Product'); ?>">
                            <?php else: ?>
                                <i class="fas fa-image" style="font-size: 24px; color: #bdc3c7;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="cart-item-details">
                            <div class="cart-item-name"><?php echo htmlspecialchars($item['name'] ?: 'Product not found'); ?></div>
                            <div class="cart-item-price">$<?php echo number_format($item['price'] ?: 0, 2); ?></div>
                        </div>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">-</button>
                            <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" onchange="updateQuantity(<?php echo $item['cart_id']; ?>, this.value)">
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                        </div>
                        <div class="cart-item-total">$<?php echo number_format(($item['price'] ?: 0) * $item['quantity'], 2); ?></div>
                        <button class="cart-item-remove" onclick="removeFromCart(<?php echo $item['cart_id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-row">
                    <span>Subtotal (<?php echo $cartCount; ?> items)</span>
                    <span>$<?php echo number_format($cartTotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="summary-row">
                    <span>Tax</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>$<?php echo number_format($cartTotal, 2); ?></span>
                </div>
            </div>

            <div class="cart-actions">
                <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>



    <script src="product.js"></script>
    <script>

        // Update quantity
        async function updateQuantity(cartItemId, quantity) {
            try {
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('cart_item_id', cartItemId);
                formData.append('quantity', quantity);

                const response = await fetch('CartController.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    location.reload(); // Reload page to show updated cart
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error updating cart:', error);
                alert('Failed to update cart');
            }
        }

        // Remove from cart
        async function removeFromCart(cartItemId) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'remove');
                formData.append('cart_item_id', cartItemId);

                const response = await fetch('CartController.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    location.reload(); // Reload page to show updated cart
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error removing from cart:', error);
                alert('Failed to remove item from cart');
            }
        }
    </script>
</body>
</html>