<?php
// Secure session handling
session_start();
session_regenerate_id(true); // Prevent session fixation

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'Database.php';
require_once 'User.php';
require_once 'Order.php';
require_once 'CartClass.php';

$database = new Database();
$db = $database->getConnection();

// Initialize classes
$user = new User($db);
$order = new Order($db);
$cart = new Cart($db);

// Get user data if not in session
if(!isset($_SESSION['first_name'])) {
    $user->id = $_SESSION['user_id'];
    if($user->readOne()) {
        $_SESSION['first_name'] = $user->first_name;
        $_SESSION['last_name'] = $user->last_name;
        $_SESSION['email'] = $user->email;
        $_SESSION['phone'] = $user->phone;
        $_SESSION['role'] = $user->role;
    } else {
        session_destroy();
        header("Location: login.php");
        exit;
    }
}

// Handle logout
if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Get user orders
$userOrders = $order->readByBuyer($_SESSION['user_id']);

// Get cart count
$cartCount = $cart->getCartCount($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Carbonsphere</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Dashboard Body Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            color: #2c3e50;
        }

        /* Dashboard Header */
        .dashboard-header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8em;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
        }

        .user-details h3 {
            margin: 0;
            font-size: 1.1em;
        }

        .user-details p {
            margin: 0;
            font-size: 0.9em;
            opacity: 0.9;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }

        /* Main Dashboard Container */
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .dashboard-welcome {
            text-align: center;
            margin-bottom: 40px;
        }

        .dashboard-welcome h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dashboard-welcome p {
            font-size: 1.2em;
            color: #7f8c8d;
            margin-bottom: 0;
        }

        /* Dashboard Navigation */
        .dashboard-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }

        .nav-link {
            padding: 12px 24px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #34495e;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(39, 174, 96, 0.1), transparent);
            transition: left 0.5s;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover,
        .nav-link.active {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.3);
        }

        /* Dashboard Sections */
        .dashboard-section {
            display: none;
            animation: fadeInUp 0.5s ease-out;
        }

        .dashboard-section.active {
            display: block;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Section Headers */
        .section-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .section-header h3 {
            font-size: 2em;
            color: #2c3e50;
            margin-bottom: 10px;
            position: relative;
        }

        .section-header h3::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #27ae60, #2ecc71);
            border-radius: 2px;
        }

        .section-header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-left: 5px solid #27ae60;
        }

        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .profile-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .profile-row:last-child {
            border-bottom: none;
        }

        .profile-row strong {
            color: #34495e;
            font-weight: 600;
        }

        .profile-row span {
            color: #7f8c8d;
        }

        .edit-profile-btn {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .edit-profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.3);
        }

        /* Orders Section */
        .orders-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .orders-grid {
            display: grid;
            gap: 20px;
        }

        .order-card {
            border: 1px solid #ecf0f1;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .order-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .order-id {
            font-weight: 600;
            color: #34495e;
        }

        .order-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 500;
            text-transform: uppercase;
        }

        .order-status.paid {
            background: #d4edda;
            color: #155724;
        }

        .order-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .order-status.shipped {
            background: #d1ecf1;
            color: #0c5460;
        }

        .order-status.delivered {
            background: #d4edda;
            color: #155724;
        }

        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .order-detail {
            display: flex;
            justify-content: space-between;
        }

        .order-detail strong {
            color: #34495e;
        }

        .order-detail span {
            color: #7f8c8d;
        }

        .order-actions {
            text-align: right;
        }

        .order-action-btn {
            background: #f8f9fa;
            color: #34495e;
            padding: 8px 16px;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .order-action-btn:hover {
            background: #27ae60;
            color: white;
            border-color: #27ae60;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 5px solid #27ae60;
        }

        .stat-icon {
            font-size: 2.5em;
            color: #27ae60;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #34495e;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #34495e;
        }

        .empty-state p {
            margin-bottom: 30px;
        }

        .empty-state .btn {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .empty-state .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
            }

            .dashboard-nav {
                flex-direction: column;
                align-items: center;
            }

            .profile-info {
                grid-template-columns: 1fr;
            }

            .order-details {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <a href="index.php" class="logo">Carbonsphere</a>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h3>
                    <p><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                </div>
                <a href="?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Main Dashboard Container -->
    <div class="dashboard-container">
        <div class="dashboard-welcome">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
            <p>Manage your account and track your orders</p>
        </div>

        <!-- Dashboard Navigation -->
        <nav class="dashboard-nav">
            <a href="#profile" class="nav-link active" onclick="showSection('profile')">Profile</a>
            <a href="#orders" class="nav-link" onclick="showSection('orders')">My Orders</a>
            <a href="products.php" class="nav-link">Browse Products</a>
            <a href="cart.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i> Cart
                <?php if($cartCount > 0): ?>
                    <span style="background: #e74c3c; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.8em; margin-left: 5px;"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
        </nav>

        <!-- Profile Section -->
        <section id="profile" class="dashboard-section active">
            <div class="section-header">
                <h3>My Profile</h3>
                <p>View and manage your account information</p>
            </div>

            <div class="profile-card">
                <div class="profile-info">
                    <div class="profile-row">
                        <strong>First Name:</strong>
                        <span><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                    </div>
                    <div class="profile-row">
                        <strong>Last Name:</strong>
                        <span><?php echo htmlspecialchars($_SESSION['last_name']); ?></span>
                    </div>
                    <div class="profile-row">
                        <strong>Email:</strong>
                        <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                    </div>
                    <div class="profile-row">
                        <strong>Phone:</strong>
                        <span><?php echo htmlspecialchars($_SESSION['phone']); ?></span>
                    </div>
                    <div class="profile-row">
                        <strong>Role:</strong>
                        <span><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></span>
                    </div>
                    <div class="profile-row">
                        <strong>Member Since:</strong>
                        <span><?php echo date('F Y', strtotime($_SESSION['created_at'] ?? 'now')); ?></span>
                    </div>
                </div>
                <div style="text-align: center;">
                    <button class="edit-profile-btn" onclick="alert('Profile editing coming soon!')">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>
            </div>
        </section>

        <!-- Orders Section -->
        <section id="orders" class="dashboard-section">
            <div class="section-header">
                <h3>My Orders</h3>
                <p>Track your order history and status</p>
            </div>

            <div class="orders-section">
                <?php if(empty($userOrders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>No orders yet</h3>
                        <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                        <a href="products.php" class="btn">Browse Products</a>
                    </div>
                <?php else: ?>
                    <div class="orders-grid">
                        <?php foreach($userOrders as $orderItem): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-id">Order #<?php echo htmlspecialchars($orderItem['order_id'] ?? $orderItem['id']); ?></div>
                                    <div class="order-status <?php echo htmlspecialchars($orderItem['status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($orderItem['status'])); ?>
                                    </div>
                                </div>
                                <div class="order-details">
                                    <div class="order-detail">
                                        <strong>Product:</strong>
                                        <span><?php echo htmlspecialchars($orderItem['product_name'] ?? 'Product'); ?></span>
                                    </div>
                                    <div class="order-detail">
                                        <strong>Quantity:</strong>
                                        <span><?php echo htmlspecialchars($orderItem['quantity']); ?></span>
                                    </div>
                                    <div class="order-detail">
                                        <strong>Unit Price:</strong>
                                        <span>₹<?php echo number_format($orderItem['unit_price'], 2); ?></span>
                                    </div>
                                    <div class="order-detail">
                                        <strong>Total:</strong>
                                        <span>₹<?php echo number_format($orderItem['total_price'], 2); ?></span>
                                    </div>
                                    <div class="order-detail">
                                        <strong>Order Date:</strong>
                                        <span><?php echo date('M j, Y', strtotime($orderItem['order_date'])); ?></span>
                                    </div>
                                    <div class="order-detail">
                                        <strong>Shipping Address:</strong>
                                        <span><?php echo htmlspecialchars($orderItem['shipping_address'] ?: 'Not provided'); ?></span>
                                    </div>
                                </div>
                                <div class="order-actions">
                                    <a href="#" class="order-action-btn" onclick="alert('Order details coming soon!')">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script>
        // Section navigation
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.dashboard-section').forEach(section => {
                section.classList.remove('active');
            });

            // Remove active class from nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });

            // Show selected section
            document.getElementById(sectionId).classList.add('active');

            // Add active class to clicked link
            event.target.classList.add('active');
        }

        // Set default active section
        document.addEventListener('DOMContentLoaded', function() {
            showSection('profile');
        });
    </script>
</body>
</html>