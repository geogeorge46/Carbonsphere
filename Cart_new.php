<?php
class Cart {
    private $conn;
    private $table_name = "cart";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add item to cart
    public function addToCart($userId, $productId, $quantity = 1) {
        // Check if product exists and has stock
        $productQuery = "SELECT stock_quantity FROM products WHERE id = ? AND status = 'active'";
        $productStmt = $this->conn->prepare($productQuery);
        $productStmt->bindParam(1, $productId);
        $productStmt->execute();

        if ($productStmt->rowCount() == 0) {
            return false; // Product not found or inactive
        }

        $product = $productStmt->fetch(PDO::FETCH_ASSOC);
        if ($product['stock_quantity'] < $quantity) {
            return false; // Insufficient stock
        }

        // Check if item already in cart
        $checkQuery = "SELECT id, quantity FROM " . $this->table_name . " WHERE user_id = ? AND product_id = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(1, $userId);
        $checkStmt->bindParam(2, $productId);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            // Update quantity
            $cartItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $newQuantity = $cartItem['quantity'] + $quantity;

            if ($product['stock_quantity'] < $newQuantity) {
                return false; // Would exceed stock
            }

            $updateQuery = "UPDATE " . $this->table_name . " SET quantity = ? WHERE id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(1, $newQuantity);
            $updateStmt->bindParam(2, $cartItem['id']);
            return $updateStmt->execute();
        } else {
            // Add new item
            $insertQuery = "INSERT INTO " . $this->table_name . " (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bindParam(1, $userId);
            $insertStmt->bindParam(2, $productId);
            $insertStmt->bindParam(3, $quantity);
            return $insertStmt->execute();
        }
    }

    // Update quantity
    public function updateQuantity($cartId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeFromCart($cartId);
        }

        // Check stock
        $stockQuery = "SELECT p.stock_quantity FROM products p
                      INNER JOIN " . $this->table_name . " c ON p.id = c.product_id
                      WHERE c.id = ?";
        $stockStmt = $this->conn->prepare($stockQuery);
        $stockStmt->bindParam(1, $cartId);
        $stockStmt->execute();

        if ($stockStmt->rowCount() == 0) {
            return false;
        }

        $stock = $stockStmt->fetch(PDO::FETCH_ASSOC);
        if ($stock['stock_quantity'] < $quantity) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . " SET quantity = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $quantity);
        $stmt->bindParam(2, $cartId);
        return $stmt->execute();
    }

    // Remove item from cart
    public function removeFromCart($cartId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $cartId);
        return $stmt->execute();
    }

    // Get cart items for user
    public function getCartItems($userId) {
        $query = "SELECT c.id as cart_id, c.quantity, p.id, p.name, p.price, p.image, p.stock_quantity
                FROM " . $this->table_name . " c
                INNER JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ? AND p.status = 'active'
                ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $userId);
        $stmt->execute();
        return $stmt;
    }

    // Get cart count for user
    public function getCartCount($userId) {
        $query = "SELECT SUM(quantity) as count FROM " . $this->table_name . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $userId);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] ?: 0;
    }

    // Get cart total for user
    public function getCartTotal($userId) {
        $query = "SELECT SUM(p.price * c.quantity) as total
                FROM " . $this->table_name . " c
                INNER JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ? AND p.status = 'active'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $userId);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?: 0;
    }

    // Clear cart for user
    public function clearCart($userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $userId);
        return $stmt->execute();
    }

    // Get cart item count (number of different items)
    public function getCartItemCount($userId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $userId);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] ?: 0;
    }
}
?>