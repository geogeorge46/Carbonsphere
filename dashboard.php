<?php
// Secure session handling
session_start();
session_regenerate_id(true); // Prevent session fixation

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if user is seller
if($_SESSION['role'] !== 'seller') {
    header("Location: index.php");
    exit;
}

require_once 'Database.php';
require_once 'User.php';
require_once 'Product.php';
require_once 'Order.php';

$database = new Database();
$db = $database->getConnection();

// Initialize classes
$user = new User($db);
$product = new Product($db);
$order = new Order($db);

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

// Handle product operations
$message = '';
$message_type = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['add_product'])) {
        $product->seller_id = $_SESSION['user_id'];
        $product->name = $_POST['name'];
        $product->description = $_POST['description'];
        $product->price = $_POST['price'];
        $product->stock_quantity = $_POST['stock_quantity'];
        $product->category = $_POST['category'];
        $product->status = 'active';

        // Handle image URL
        if(!empty($_POST['image'])) {
            $product->image = $_POST['image'];
        }

        $errors = $product->validate();
        if(empty($errors)) {
            if($product->create()) {
                $message = "Product added successfully!";
                $message_type = "success";
            } else {
                $message = "Failed to add product. Please try again.";
                $message_type = "error";
            }
        } else {
            $message = implode("<br>", $errors);
            $message_type = "error";
        }
    }

    if(isset($_POST['update_product'])) {
        $product->id = $_POST['product_id'];
        $product->name = $_POST['name'];
        $product->description = $_POST['description'];
        $product->price = $_POST['price'];
        $product->stock_quantity = $_POST['stock_quantity'];
        $product->category = $_POST['category'];
        $product->status = $_POST['status'];

        // Handle image URL
        if(!empty($_POST['image'])) {
            $product->image = $_POST['image'];
        }

        $errors = $product->validate();
        if(empty($errors)) {
            if($product->update()) {
                $message = "Product updated successfully!";
                $message_type = "success";
            } else {
                $message = "Failed to update product. Please try again.";
                $message_type = "error";
            }
        } else {
            $message = implode("<br>", $errors);
            $message_type = "error";
        }
    }

    if(isset($_POST['delete_product'])) {
        $product->id = $_POST['product_id'];
        if($product->delete()) {
            $message = "Product deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Failed to delete product. Please try again.";
            $message_type = "error";
        }
    }

    if(isset($_POST['update_order_status'])) {
        $order->id = $_POST['order_id'];
        $order->status = $_POST['status'];
        if($order->updateStatus()) {
            $message = "Order status updated successfully!";
            $message_type = "success";
        } else {
            $message = "Failed to update order status. Please try again.";
            $message_type = "error";
        }
    }
}

// Get dashboard data
$product_count = $product->getCountBySeller($_SESSION['user_id']);
$order_stats = $order->getSellerStats($_SESSION['user_id']);
$categories = $product->getCategories();
$products = $product->readBySeller($_SESSION['user_id']);
$orders = $order->readBySeller($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carbonsphere - Dashboard</title>
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
            color: #2c3e50;
            font-weight: 500;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 30px 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #27ae60, #2ecc71);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .stat-icon {
            font-size: 2.5em;
            color: #27ae60;
            margin-bottom: 15px;
        }

        .stat-card h5 {
            margin: 0 0 15px 0;
            color: #7f8c8d;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #95a5a6;
            font-size: 0.9em;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 15px;
            margin: 30px 0;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-primary,
        .btn-secondary,
        .btn-success,
        .btn-info {
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #229954 0%, #27ae60 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #2980b9 0%, #21618c 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(243, 156, 18, 0.4);
        }

        .btn-info {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(155, 89, 182, 0.3);
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #8e44ad 0%, #6c3483 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(155, 89, 182, 0.4);
        }

        /* Features List */
        .features-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin: 30px 0;
        }

        .features-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .features-list li {
            padding: 15px 0;
            border-bottom: 1px solid #ecf0f1;
            color: #34495e;
            font-size: 1.1em;
            display: flex;
            align-items: center;
        }

        .features-list li:last-child {
            border-bottom: none;
        }

        .features-list li::before {
            content: '🌱';
            margin-right: 15px;
            font-size: 1.2em;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }

        .empty-state::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #bdc3c7, #95a5a6);
        }

        .empty-icon {
            font-size: 4em;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.5em;
        }

        .empty-state p {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 1.1em;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Settings */
        .settings-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .setting-item {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 5px solid #3498db;
            position: relative;
            overflow: hidden;
        }

        .setting-item::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), transparent);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }

        .setting-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .setting-icon {
            font-size: 2em;
            color: #3498db;
            margin-bottom: 15px;
        }

        .setting-item h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 1.3em;
        }

        .setting-item p {
            margin: 0 0 20px 0;
            color: #7f8c8d;
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .user-info {
                justify-content: center;
            }

            .dashboard-nav {
                padding: 15px;
            }

            .nav-link {
                padding: 10px 15px;
                font-size: 14px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .profile-info {
                grid-template-columns: 1fr;
            }

            .settings-options {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                flex-direction: column;
                align-items: center;
            }

            .profile-actions {
                flex-direction: column;
                align-items: center;
            }

            .dashboard-welcome h1 {
                font-size: 2em;
            }
        }

        /* Message Cards */
        .message-card {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease-out;
        }

        .message-card.success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-left: 4px solid #27ae60;
            color: #155724;
        }

        .message-card.error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border-left: 4px solid #e74c3c;
            color: #721c24;
        }

        .message-icon {
            margin-right: 15px;
            font-size: 1.5em;
        }

        .message-content {
            flex: 1;
        }

        .message-close {
            background: none;
            border: none;
            font-size: 1.2em;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .message-close:hover {
            opacity: 1;
        }

        /* Products Table */
        .products-actions {
            margin-bottom: 20px;
        }

        .products-table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .products-table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }

        .products-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .product-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #ecf0f1;
        }

        .no-image {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #bdc3c7;
            border: 2px solid #ecf0f1;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.active {
            background-color: #27ae60;
            color: white;
        }

        .status-badge.inactive {
            background-color: #e74c3c;
            color: white;
        }

        .status-badge.order-status {
            color: white;
            font-size: 0.75em;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-small {
            padding: 8px 12px;
            font-size: 0.8em;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
        }

        /* Orders Table */
        .orders-table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .orders-table th {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }

        .orders-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease-out;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease-out;
        }

        .modal-header {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            padding: 20px 30px;
            border-radius: 15px 15px 0 0;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.5em;
        }

        .modal-body {
            padding: 30px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 20px 30px;
            background-color: #f8f9fa;
            border-radius: 0 0 15px 15px;
            text-align: right;
        }

        .close {
            color: white;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            opacity: 0.7;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #27ae60;
            box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
            }

            .products-table,
            .orders-table {
                font-size: 0.9em;
            }

            .products-table th,
            .products-table td,
            .orders-table th,
            .orders-table td {
                padding: 10px;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 20px 15px;
            }

            .profile-card,
            .features-card,
            .empty-state,
            .setting-item {
                padding: 20px;
            }

            .stat-card {
                padding: 20px;
            }

            .btn-primary,
            .btn-secondary,
            .btn-success,
            .btn-info {
                padding: 12px 20px;
                font-size: 14px;
            }

            .modal-body {
                padding: 20px;
            }

            .modal-header {
                padding: 15px 20px;
            }

            .modal-footer {
                padding: 15px 20px;
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
                    <p><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?> Account</p>
                </div>
                <a href="?logout=true" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Dashboard Container -->
    <div class="dashboard-container">
        <!-- Message Display -->
        <?php if(!empty($message)): ?>
        <div class="message-card <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
            <div class="message-icon">
                <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            </div>
            <div class="message-content">
                <?php echo $message; ?>
            </div>
            <button class="message-close" onclick="this.parentElement.style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>

        <div class="dashboard-welcome">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
            <p>Manage your products and track your sales performance.</p>
        </div>

        <!-- Navigation Menu -->
        <nav class="dashboard-nav">
            <a href="#profile" class="nav-link active">Profile</a>
            <a href="#dashboard" class="nav-link">Dashboard</a>
            <?php if($_SESSION['role'] === 'seller'): ?>
                <a href="#products" class="nav-link">My Products</a>
                <a href="#orders" class="nav-link">Orders</a>
            <?php else: ?>
                <a href="#orders" class="nav-link">My Orders</a>
                <a href="#favorites" class="nav-link">Favorites</a>
            <?php endif; ?>
            <a href="#settings" class="nav-link">Settings</a>
        </nav>

        <div class="dashboard">
            <!-- Profile Section -->
            <div id="profile" class="dashboard-section active">
                <div class="section-header">
                    <h3>Your Profile</h3>
                    <p>View and manage your account information</p>
                </div>
                <div class="profile-card">
                    <div class="profile-info">
                        <div class="profile-row">
                            <strong>User ID:</strong> <span><?php echo htmlspecialchars($_SESSION['user_id_display']); ?></span>
                        </div>
                        <div class="profile-row">
                            <strong>Full Name:</strong> <span><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
                        </div>
                        <div class="profile-row">
                            <strong>Email:</strong> <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                        </div>
                        <div class="profile-row">
                            <strong>Phone:</strong> <span><?php echo htmlspecialchars($_SESSION['phone']); ?></span>
                        </div>
                        <div class="profile-row">
                            <strong>Account Type:</strong> <span><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?> Account</span>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <button class="btn-secondary"><i class="fas fa-edit"></i> Edit Profile</button>
                        <button class="btn-info"><i class="fas fa-key"></i> Change Password</button>
                    </div>
                </div>
            </div>

            <!-- Dashboard Section -->
            <div id="dashboard" class="dashboard-section">
                <div class="section-header">
                    <h3><?php echo $_SESSION['role'] === 'seller' ? 'Seller Dashboard' : 'User Dashboard'; ?></h3>
                    <p><?php echo $_SESSION['role'] === 'seller' ? 'Manage your products and track your sales performance' : 'Track your progress and explore eco-friendly options'; ?></p>
                </div>

                <?php if($_SESSION['role'] === 'seller'): ?>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-box"></i></div>
                            <h5>Total Products</h5>
                            <div class="stat-number"><?php echo $product_count; ?></div>
                            <div class="stat-label">Listed for sale</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                            <h5>Total Orders</h5>
                            <div class="stat-number"><?php echo $order_stats['total_orders']; ?></div>
                            <div class="stat-label">Orders received</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                            <h5>Revenue</h5>
                            <div class="stat-number">$<?php echo number_format($order_stats['total_revenue'], 2); ?></div>
                            <div class="stat-label">Total earnings</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-clock"></i></div>
                            <h5>Pending Orders</h5>
                            <div class="stat-number"><?php echo $order_stats['pending_orders']; ?></div>
                            <div class="stat-label">Awaiting action</div>
                        </div>
                    </div>
                    <div class="quick-actions">
                        <button class="btn-primary"><i class="fas fa-plus"></i> Add New Product</button>
                        <button class="btn-secondary"><i class="fas fa-list"></i> View All Orders</button>
                        <button class="btn-success"><i class="fas fa-chart-line"></i> Analytics</button>
                    </div>
                <?php else: ?>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                            <h5>Orders Placed</h5>
                            <div class="stat-number">0</div>
                            <div class="stat-label">Total purchases</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-heart"></i></div>
                            <h5>Wishlist Items</h5>
                            <div class="stat-number">0</div>
                            <div class="stat-label">Saved products</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-leaf"></i></div>
                            <h5>Carbon Saved</h5>
                            <div class="stat-number">0 kg</div>
                            <div class="stat-label">CO₂ reduced</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                            <h5>Points Earned</h5>
                            <div class="stat-number">0</div>
                            <div class="stat-label">Reward points</div>
                        </div>
                    </div>
                    <div class="quick-actions">
                        <button class="btn-primary"><i class="fas fa-search"></i> Browse Products</button>
                        <button class="btn-secondary"><i class="fas fa-calculator"></i> Track Carbon Footprint</button>
                        <button class="btn-info"><i class="fas fa-graduation-cap"></i> Educational Resources</button>
                    </div>
                <?php endif; ?>

                <div class="features-card">
                    <div class="section-header">
                        <h3>Platform Features</h3>
                        <p>Discover what Carbonsphere has to offer</p>
                    </div>
                    <ul class="features-list">
                        <?php if($_SESSION['role'] === 'seller'): ?>
                            <li>Sell eco-friendly products to conscious consumers</li>
                            <li>Manage your product catalog with ease</li>
                            <li>Track sales performance and analytics</li>
                            <li>Communicate directly with customers</li>
                            <li>Secure payment processing and management</li>
                        <?php else: ?>
                            <li>Track your daily carbon footprint accurately</li>
                            <li>Learn about environmental impact through education</li>
                            <li>Purchase verified eco-friendly products</li>
                            <li>Connect with a community of eco-conscious individuals</li>
                            <li>Access comprehensive educational resources</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Products Section (for sellers) -->
            <?php if($_SESSION['role'] === 'seller'): ?>
            <div id="products" class="dashboard-section">
                <div class="section-header">
                    <h3>My Products</h3>
                    <p>Manage your eco-friendly product listings</p>
                </div>

                <div class="products-actions">
                    <button class="btn-primary" onclick="showAddProductModal()">
                        <i class="fas fa-plus"></i> Add New Product
                    </button>
                </div>

                <?php if($products->rowCount() > 0): ?>
                <div class="products-table-container">
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $products->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                                <td>
                                    <?php if($row['image']): ?>
                                        <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Product" class="product-thumbnail">
                                    <?php else: ?>
                                        <div class="no-image"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>$<?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['stock_quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $row['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-secondary btn-small" onclick="editProduct(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-danger btn-small" onclick="deleteProduct(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-box-open"></i></div>
                    <h4>No Products Yet</h4>
                    <p>You haven't added any products yet. Start selling eco-friendly products to make a positive impact!</p>
                    <button class="btn-primary" onclick="showAddProductModal()">
                        <i class="fas fa-plus"></i> Add Your First Product
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Orders Section -->
            <div id="orders" class="dashboard-section">
                <div class="section-header">
                    <h3>Customer Orders</h3>
                    <p>Track and manage customer orders for your products</p>
                </div>

                <?php if($orders->rowCount() > 0): ?>
                <div class="orders-table-container">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $orders->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                <td>$<?php echo number_format($row['total_price'], 2); ?></td>
                                <td>
                                    <span class="status-badge order-status" style="background-color: <?php echo Order::getStatusColor($row['status']); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-secondary btn-small" onclick="viewOrderDetails(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if($row['status'] !== 'delivered' && $row['status'] !== 'cancelled'): ?>
                                        <button class="btn-info btn-small" onclick="updateOrderStatus(<?php echo $row['id']; ?>, '<?php echo $row['status']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-shopping-cart"></i></div>
                    <h4>No Orders Yet</h4>
                    <p>No orders received yet. Your products will appear here once customers start purchasing.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Favorites Section (for users) -->
            <?php if($_SESSION['role'] !== 'seller'): ?>
            <div id="favorites" class="dashboard-section">
                <div class="section-header">
                    <h3>My Favorites</h3>
                    <p>Your saved eco-friendly products</p>
                </div>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-heart"></i></div>
                    <h4>No Favorites Yet</h4>
                    <p>You haven't added any favorites yet. Browse products and save the ones you love!</p>
                    <button class="btn-primary"><i class="fas fa-search"></i> Browse Products</button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Settings Section -->
            <div id="settings" class="dashboard-section">
                <div class="section-header">
                    <h3>Account Settings</h3>
                    <p>Manage your account preferences and security</p>
                </div>
                <div class="settings-options">
                    <div class="setting-item">
                        <div class="setting-icon"><i class="fas fa-user-edit"></i></div>
                        <h4>Profile Information</h4>
                        <p>Update your personal information and account details</p>
                        <button class="btn-secondary"><i class="fas fa-edit"></i> Edit Profile</button>
                    </div>
                    <div class="setting-item">
                        <div class="setting-icon"><i class="fas fa-shield-alt"></i></div>
                        <h4>Security</h4>
                        <p>Change password and manage security settings</p>
                        <button class="btn-info"><i class="fas fa-key"></i> Change Password</button>
                    </div>
                    <div class="setting-item">
                        <div class="setting-icon"><i class="fas fa-bell"></i></div>
                        <h4>Notifications</h4>
                        <p>Manage your notification preferences</p>
                        <button class="btn-secondary"><i class="fas fa-cog"></i> Notification Settings</button>
                    </div>
                    <div class="setting-item">
                        <div class="setting-icon"><i class="fas fa-lock"></i></div>
                        <h4>Privacy</h4>
                        <p>Control your privacy and data sharing settings</p>
                        <button class="btn-secondary"><i class="fas fa-user-shield"></i> Privacy Settings</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Navigation functionality
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Remove active class from all links and sections
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                document.querySelectorAll('.dashboard-section').forEach(s => s.classList.remove('active'));

                // Add active class to clicked link
                this.classList.add('active');

                // Show corresponding section
                const targetId = this.getAttribute('href').substring(1);
                document.getElementById(targetId).classList.add('active');
            });
        });

        // Modal functionality
        function showAddProductModal() {
            document.getElementById('productModal').style.display = 'block';
            document.getElementById('productForm').reset();
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('product_id').value = '';

            // Show add button, hide update button
            document.querySelector('button[name="add_product"]').style.display = 'inline-block';
            document.querySelector('button[name="update_product"]').style.display = 'none';
        }

        function editProduct(productId) {
            // Fetch product data and populate form
            fetch(`get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        const product = data.product;
                        document.getElementById('modalTitle').textContent = 'Edit Product';
                        document.getElementById('product_id').value = product.id;
                        document.getElementById('name').value = product.name;
                        document.getElementById('description').value = product.description;
                        document.getElementById('price').value = product.price;
                        document.getElementById('stock_quantity').value = product.stock_quantity;
                        document.getElementById('category').value = product.category;
                        document.getElementById('status').value = product.status;

                        // Show update button, hide add button
                        document.querySelector('button[name="add_product"]').style.display = 'none';
                        document.querySelector('button[name="update_product"]').style.display = 'inline-block';

                        document.getElementById('productModal').style.display = 'block';
                    } else {
                        alert('Failed to load product data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load product data');
                });
        }

        function deleteProduct(productId, productName) {
            if(confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="product_id" value="${productId}">
                    <input type="hidden" name="delete_product" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function updateOrderStatus(orderId, currentStatus) {
            const newStatus = prompt(`Update order status (current: ${currentStatus}). Enter new status:`, currentStatus);
            if(newStatus && newStatus !== currentStatus) {
                const validStatuses = ['pending', 'processed', 'shipped', 'delivered'];
                if(validStatuses.includes(newStatus)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="order_id" value="${orderId}">
                        <input type="hidden" name="status" value="${newStatus}">
                        <input type="hidden" name="update_order_status" value="1">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                } else {
                    alert('Invalid status. Valid statuses: pending, processed, shipped, delivered');
                }
            }
        }

        function viewOrderDetails(orderId) {
            // This could open a modal with full order details
            alert('Order details view will be implemented');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for(let modal of modals) {
                if(event.target === modal) {
                    modal.style.display = 'none';
                }
            }
        }

        // Close modal with close button
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.onclick = function() {
                this.closest('.modal').style.display = 'none';
            }
        });
    </script>

    <!-- Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h3 id="modalTitle">Add New Product</h3>
            </div>
            <form id="productForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="product_id" value="">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Category *</label>
                            <select id="category" name="category" required>
                                <option value="">Select Category</option>
                                <?php while($cat = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price ($) *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity *</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" min="0" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="image">Product Image URL</label>
                            <input type="url" id="image" name="image" placeholder="https://example.com/image.jpg">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('productModal').style.display='none'">Cancel</button>
                    <button type="submit" name="add_product" class="btn-primary">Save Product</button>
                    <button type="submit" name="update_product" class="btn-primary" style="display:none;">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>