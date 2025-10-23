<?php
require_once 'session_check.php';
requireAuth();

$orderId = $_GET['order_id'] ?? '';

if (empty($orderId)) {
    header('Location: products.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Carbonsphere</title>
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

        /* Confirmation Page Styles */
        .confirmation-header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .confirmation-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 30px;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #27ae60;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
            color: white;
        }

        .confirmation-title {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .confirmation-subtitle {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }

        .order-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .order-number {
            font-size: 24px;
            font-weight: 600;
            color: #27ae60;
            margin-bottom: 20px;
        }

        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            text-align: left;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .info-value {
            color: #666;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            padding: 12px 24px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }

        .btn-secondary {
            padding: 12px 24px;
            background: transparent;
            color: #27ae60;
            border: 2px solid #27ae60;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #27ae60;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }

        .next-steps {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .steps-title {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .steps-list {
            list-style: none;
            padding: 0;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-left: 30px;
            position: relative;
        }

        .step-number {
            position: absolute;
            left: 0;
            top: 0;
            width: 24px;
            height: 24px;
            background: #27ae60;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .step-content {
            color: #666;
            line-height: 1.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .order-info {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .confirmation-title {
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php endif; ?>
            </ul>
            <div class="nav-auth">
                <a href="logout.php" class="btn-nav btn-nav-login">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="confirmation-header">
        <div class="container">
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="confirmation-title">Your Order Has Been Placed Successfully</h2>
            <p class="confirmation-subtitle">We've sent a confirmation email with your order details</p>
        </div>

        <div class="order-details">
            <div class="order-number">Order #<?php echo htmlspecialchars($orderId); ?></div>

            <div class="order-info">
                <div class="info-item">
                    <div class="info-label">Order Date</div>
                    <div class="info-value"><?php echo date('F j, Y'); ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value">Razorpay</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Order Status</div>
                    <div class="info-value">Processing</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Estimated Delivery</div>
                    <div class="info-value">3-5 Business Days</div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="products.php" class="btn-primary">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
            <a href="dashboard.php" class="btn-secondary">
                <i class="fas fa-user"></i> View My Orders
            </a>
        </div>

        <div class="next-steps">
            <h3 class="steps-title">What Happens Next?</h3>
            <ul class="steps-list">
                <li class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-content">You'll receive an email confirmation with your order details and tracking information.</div>
                </li>
                <li class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-content">Our team will process your order and prepare it for shipping within 1-2 business days.</div>
                </li>
                <li class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-content">Once shipped, you'll receive a tracking number to monitor your package's journey.</div>
                </li>
                <li class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-content">Your eco-friendly products will be delivered to your doorstep!</div>
                </li>
            </ul>
        </div>
    </div>

    <script>
        // Auto-redirect after 10 seconds
        setTimeout(() => {
            window.location.href = 'products.php';
        }, 10000);
    </script>
</body>
</html>