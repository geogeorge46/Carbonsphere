<?php
class Order {
    private $conn;
    private $table_name = "orders";

    // Order properties
    public $id;
    public $order_id;
    public $buyer_id;
    public $seller_id;
    public $product_id;
    public $quantity;
    public $unit_price;
    public $total_price;
    public $status;
    public $order_date;
    public $updated_at;
    public $shipping_address;
    public $notes;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create order
    public function create() {
        // Generate unique order ID
        $this->order_id = 'ORD' . date('Y') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

        $query = "INSERT INTO " . $this->table_name . "
                SET
                    order_id=:order_id,
                    buyer_id=:buyer_id,
                    seller_id=:seller_id,
                    product_id=:product_id,
                    quantity=:quantity,
                    unit_price=:unit_price,
                    total_price=:total_price,
                    status=:status,
                    shipping_address=:shipping_address,
                    notes=:notes";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->order_id = htmlspecialchars(strip_tags($this->order_id));
        $this->buyer_id = htmlspecialchars(strip_tags($this->buyer_id));
        $this->seller_id = htmlspecialchars(strip_tags($this->seller_id));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->unit_price = htmlspecialchars(strip_tags($this->unit_price));
        $this->total_price = htmlspecialchars(strip_tags($this->total_price));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->shipping_address = htmlspecialchars(strip_tags($this->shipping_address));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        // Bind values
        $stmt->bindParam(":order_id", $this->order_id);
        $stmt->bindParam(":buyer_id", $this->buyer_id);
        $stmt->bindParam(":seller_id", $this->seller_id);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit_price", $this->unit_price);
        $stmt->bindParam(":total_price", $this->total_price);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":shipping_address", $this->shipping_address);
        $stmt->bindParam(":notes", $this->notes);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Read orders by seller
    public function readBySeller($seller_id) {
        $query = "SELECT o.*, p.name as product_name, u.first_name, u.last_name, u.email
                FROM " . $this->table_name . " o
                LEFT JOIN products p ON o.product_id = p.id
                LEFT JOIN users u ON o.buyer_id = u.id
                WHERE o.seller_id = ?
                ORDER BY o.order_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();

        return $stmt;
    }

    // Read single order
    public function readOne() {
        $query = "SELECT o.*, p.name as product_name, p.image as product_image,
                         u.first_name, u.last_name, u.email, u.phone
                FROM " . $this->table_name . " o
                LEFT JOIN products p ON o.product_id = p.id
                LEFT JOIN users u ON o.buyer_id = u.id
                WHERE o.id = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->order_id = $row['order_id'];
            $this->buyer_id = $row['buyer_id'];
            $this->seller_id = $row['seller_id'];
            $this->product_id = $row['product_id'];
            $this->quantity = $row['quantity'];
            $this->unit_price = $row['unit_price'];
            $this->total_price = $row['total_price'];
            $this->status = $row['status'];
            $this->order_date = $row['order_date'];
            $this->updated_at = $row['updated_at'];
            $this->shipping_address = $row['shipping_address'];
            $this->notes = $row['notes'];
            return $row;
        }
        return false;
    }

    // Update order status
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get order statistics for seller
    public function getSellerStats($seller_id) {
        $stats = [];

        // Total orders
        $query = "SELECT COUNT(*) as total_orders FROM " . $this->table_name . " WHERE seller_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_orders'] = $row['total_orders'];

        // Pending orders
        $query = "SELECT COUNT(*) as pending_orders FROM " . $this->table_name . " WHERE seller_id = ? AND status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['pending_orders'] = $row['pending_orders'];

        // Total revenue
        $query = "SELECT SUM(total_price) as total_revenue FROM " . $this->table_name . " WHERE seller_id = ? AND status != 'cancelled'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_revenue'] = $row['total_revenue'] ?: 0;

        return $stats;
    }

    // Validate order data
    public function validate() {
        $errors = [];

        if(!is_numeric($this->quantity) || $this->quantity <= 0) {
            $errors[] = "Quantity must be a positive number";
        }

        if(!is_numeric($this->unit_price) || $this->unit_price <= 0) {
            $errors[] = "Unit price must be a positive number";
        }

        if(!is_numeric($this->total_price) || $this->total_price <= 0) {
            $errors[] = "Total price must be a positive number";
        }

        $valid_statuses = ['pending', 'processed', 'shipped', 'delivered', 'cancelled'];
        if(!in_array($this->status, $valid_statuses)) {
            $errors[] = "Invalid order status";
        }

        return $errors;
    }

    // Get status color for display
    public static function getStatusColor($status) {
        $colors = [
            'pending' => '#f39c12',
            'processed' => '#3498db',
            'shipped' => '#9b59b6',
            'delivered' => '#27ae60',
            'cancelled' => '#e74c3c'
        ];
        return isset($colors[$status]) ? $colors[$status] : '#95a5a6';
    }

    // Get status badge class
    public static function getStatusBadge($status) {
        $badges = [
            'pending' => 'badge-warning',
            'processed' => 'badge-info',
            'shipped' => 'badge-primary',
            'delivered' => 'badge-success',
            'cancelled' => 'badge-danger'
        ];
        return isset($badges[$status]) ? $badges[$status] : 'badge-secondary';
    }

    // Read orders by buyer
    public function readByBuyer($buyerId) {
        $query = "SELECT o.*, p.name as product_name, p.image as product_image
                FROM " . $this->table_name . " o
                LEFT JOIN products p ON o.product_id = p.id
                WHERE o.buyer_id = ?
                ORDER BY o.order_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $buyerId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get buyer statistics
    public function getBuyerStats($buyerId) {
        $stats = [
            'total_orders' => 0,
            'pending_orders' => 0,
            'delivered_orders' => 0,
            'total_spent' => 0
        ];

        // Total orders
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE buyer_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $buyerId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_orders'] = $result['count'];

        // Pending orders
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE buyer_id = ? AND status IN ('pending', 'paid', 'shipped')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $buyerId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['pending_orders'] = $result['count'];

        // Delivered orders
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE buyer_id = ? AND status = 'delivered'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $buyerId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['delivered_orders'] = $result['count'];

        // Total spent
        $query = "SELECT SUM(total_price) as total FROM " . $this->table_name . " WHERE buyer_id = ? AND status != 'cancelled'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $buyerId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_spent'] = $result['total'] ?: 0;

        return $stats;
    }
}
?>