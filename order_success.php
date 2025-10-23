<?php
session_start();
require_once 'session_check.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$orderId = $_GET['order_id'] ?? '';
if (empty($orderId)) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Carbonsphere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 50px;
            text-align: center;
            max-width: 600px;
            width: 90%;
            animation: fadeInUp 0.8s ease-out;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }

        .success-icon i {
            font-size: 40px;
            color: white;
        }

        .success-container h1 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .success-container p {
            color: #6c757d;
            font-size: 1.1em;
            margin-bottom: 30px;
        }

        .order-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .order-details h4 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .order-details p {
            margin-bottom: 5px;
            color: #495057;
        }

        .order-details strong {
            color: #2c3e50;
        }

        .success-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .success-actions .btn {
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }

        .btn-outline-success {
            border-width: 2px;
            color: #28a745;
            border-color: #28a745;
        }

        .btn-outline-success:hover {
            background: #28a745;
            border-color: #28a745;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 576px) {
            .success-container {
                padding: 30px 20px;
            }

            .success-actions {
                flex-direction: column;
                align-items: center;
            }

            .success-actions .btn {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1>Payment Successful!</h1>
        <p>Thank you for your purchase. Your order has been confirmed and is being processed.</p>

        <div class="order-details">
            <h4>Order Information</h4>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($orderId); ?></p>
            <p><strong>Payment Status:</strong> <span class="badge bg-success">Paid</span></p>
            <p><strong>Estimated Delivery:</strong> 3-5 business days</p>
        </div>

        <div class="success-actions">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Continue Shopping
            </a>
            <a href="dashboard.php" class="btn btn-outline-success">
                <i class="fas fa-user"></i> View Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>