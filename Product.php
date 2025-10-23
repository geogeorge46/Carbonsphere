<?php
class Product {
    private $conn;
    private $table_name = "products";

    // Product properties
    public $id;
    public $product_id;
    public $seller_id;
    public $name;
    public $description;
    public $price;
    public $stock_quantity;
    public $category;
    public $image;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Generate unique product ID
    private function generateProductId() {
        do {
            $product_id = 'PROD' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while ($this->productIdExists($product_id));
        return $product_id;
    }

    // Check if product ID exists
    private function productIdExists($product_id) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE product_id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $product_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Create product
    public function create() {
        // Generate unique product ID
        $this->product_id = $this->generateProductId();

        $query = "INSERT INTO " . $this->table_name . "
                SET
                    product_id=:product_id,
                    seller_id=:seller_id,
                    name=:name,
                    description=:description,
                    price=:price,
                    stock_quantity=:stock_quantity,
                    category=:category,
                    image=:image,
                    status=:status";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->seller_id = htmlspecialchars(strip_tags($this->seller_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->stock_quantity = htmlspecialchars(strip_tags($this->stock_quantity));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind values
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":seller_id", $this->seller_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock_quantity", $this->stock_quantity);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Read all products by seller
    public function readBySeller($seller_id) {
        $query = "SELECT p.*, c.name as category_name
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category = c.name
                WHERE p.seller_id = ?
                ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();

        return $stmt;
    }

    // Read single product
    public function readOne() {
        $query = "SELECT p.*, c.name as category_name
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category = c.name
                WHERE p.id = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->product_id = $row['product_id'];
            $this->seller_id = $row['seller_id'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->stock_quantity = $row['stock_quantity'];
            $this->category = $row['category'];
            $this->image = $row['image'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Update product
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    name=:name,
                    description=:description,
                    price=:price,
                    stock_quantity=:stock_quantity,
                    category=:category,
                    image=:image,
                    status=:status
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->stock_quantity = htmlspecialchars(strip_tags($this->stock_quantity));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock_quantity", $this->stock_quantity);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete product
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get product count by seller
    public function getCountBySeller($seller_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE seller_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $seller_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Read all products with optional filters
    public function readAll($filters = []) {
        $query = "SELECT p.*, c.name as category_name, u.first_name, u.last_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category = c.name
                  LEFT JOIN users u ON p.seller_id = u.id
                  WHERE p.status = 'active'";

        $params = [];

        // Add search filter
        if (!empty($filters['search'])) {
            $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Add category filter
        if (!empty($filters['category'])) {
            $query .= " AND p.category = ?";
            $params[] = $filters['category'];
        }

        // Add price range filter
        if (!empty($filters['min_price'])) {
            $query .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $query .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }

        $query .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        for ($i = 0; $i < count($params); $i++) {
            $stmt->bindParam($i + 1, $params[$i]);
        }

        $stmt->execute();
        return $stmt;
    }

    // Get categories
    public function getCategories() {
        $query = "SELECT * FROM categories ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Validate product data
    public function validate() {
        $errors = [];

        if(empty($this->name) || strlen($this->name) < 3) {
            $errors[] = "Product name must be at least 3 characters long";
        }

        if(empty($this->description) || strlen($this->description) < 10) {
            $errors[] = "Product description must be at least 10 characters long";
        }

        if(!is_numeric($this->price) || $this->price <= 0) {
            $errors[] = "Price must be a positive number";
        }

        if(!is_numeric($this->stock_quantity) || $this->stock_quantity < 0) {
            $errors[] = "Stock quantity must be a non-negative number";
        }

        if(empty($this->category)) {
            $errors[] = "Category is required";
        }

        return $errors;
    }

    // Upload image
    public function uploadImage($file, $target_dir = "uploads/products/") {
        if(!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Create directory if it doesn't exist
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Generate unique filename
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if(!in_array($file_extension, $allowed_extensions)) {
            return false;
        }

        $filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $filename;

        // Check file size (max 5MB)
        if($file['size'] > 5000000) {
            return false;
        }

        if(move_uploaded_file($file['tmp_name'], $target_file)) {
            return $target_file;
        }

        return false;
    }
}
?>