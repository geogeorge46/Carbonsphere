<?php
session_start();
require_once 'Database.php';
require_once 'CartClass.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    $cart = new Cart($db);
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Database connection error.'];
    echo json_encode($response);
    exit;
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Please login to manage your cart.';
        echo json_encode($response);
        exit;
    }

    $userId = $_SESSION['user_id'];

    try {
        switch ($action) {
            case 'add':
                $productId = $_POST['product_id'] ?? 0;
                $quantity = $_POST['quantity'] ?? 1;

                if ($productId > 0) {
                    if ($cart->addToCart($userId, $productId, $quantity)) {
                        $response['success'] = true;
                        $response['message'] = 'Product added to cart!';
                        $response['cart_count'] = $cart->getCartCount($userId);
                    } else {
                        $response['message'] = 'Failed to add product to cart.';
                    }
                } else {
                    $response['message'] = 'Invalid product ID.';
                }
                break;

            case 'remove':
                $cartItemId = $_POST['cart_item_id'] ?? 0;

                if ($cartItemId > 0) {
                    if ($cart->removeFromCart($cartItemId)) {
                        $response['success'] = true;
                        $response['message'] = 'Item removed from cart.';
                        $response['cart_count'] = $cart->getCartCount($userId);
                    } else {
                        $response['message'] = 'Failed to remove item from cart.';
                    }
                } else {
                    $response['message'] = 'Invalid cart item ID.';
                }
                break;

            case 'update':
                $cartItemId = $_POST['cart_item_id'] ?? 0;
                $quantity = $_POST['quantity'] ?? 1;

                if ($cartItemId > 0 && $quantity > 0) {
                    if ($cart->updateQuantity($cartItemId, $quantity)) {
                        $response['success'] = true;
                        $response['message'] = 'Cart updated successfully.';
                        $response['cart_count'] = $cart->getCartCount($userId);
                    } else {
                        $response['message'] = 'Failed to update cart.';
                    }
                } else {
                    $response['message'] = 'Invalid parameters.';
                }
                break;

            case 'clear':
                if ($cart->clearCart($userId)) {
                    $response['success'] = true;
                    $response['message'] = 'Cart cleared successfully.';
                    $response['cart_count'] = 0;
                } else {
                    $response['message'] = 'Failed to clear cart.';
                }
                break;

            default:
                $response['message'] = 'Invalid action.';
        }
    } catch (Exception $e) {
        $response['message'] = 'An error occurred while processing your request.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Please login to view your cart.';
        echo json_encode($response);
        exit;
    }

    $userId = $_SESSION['user_id'];

    try {
        switch ($action) {
            case 'get_items':
                $stmt = $cart->getCartItems($userId);
                $items = [];

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $items[] = $row;
                }

                $response['success'] = true;
                $response['items'] = $items;
                $response['cart_count'] = $cart->getCartCount($userId);
                break;

            case 'get_count':
                $response['success'] = true;
                $response['cart_count'] = $cart->getCartCount($userId);
                break;

            default:
                $response['message'] = 'Invalid action.';
        }
    } catch (Exception $e) {
        $response['message'] = 'An error occurred while retrieving cart data.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>