<?php
require_once 'Database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $user_id;
    public $username;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $password;
    public $role;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Generate unique user ID
    private function generateUserId() {
        return 'USER' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    // Validation methods
    public function validateFirstName($first_name) {
        if (empty($first_name)) {
            return "First name is required.";
        }
        if (!preg_match("/^[a-zA-Z\s]+$/", $first_name)) {
            return "First name should contain only alphabets and spaces.";
        }
        if (strlen($first_name) < 3) {
            return "First name should be at least 3 characters long.";
        }
        return true;
    }

    public function validateLastName($last_name) {
        if (empty($last_name)) {
            return "Last name is required.";
        }
        if (!preg_match("/^[a-zA-Z\s]+$/", $last_name)) {
            return "Last name should contain only alphabets and spaces.";
        }
        if (strlen($last_name) < 3) {
            return "Last name should be at least 3 characters long.";
        }
        return true;
    }

    public function validateEmail($email) {
        if (empty($email)) {
            return "Email is required.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Please enter a valid email address.";
        }
        return true;
    }

    public function validatePhone($phone) {
        if (empty($phone)) {
            return "Phone number is required.";
        }
        if (!preg_match("/^[6-9][0-9]{9}$/", $phone)) {
            return "Phone number should be 10 digits and start with 6, 7, 8, or 9.";
        }
        return true;
    }

    public function validatePassword($password) {
        if (empty($password)) {
            return "Password is required.";
        }
        if (strlen($password) < 6) {
            return "Password should be at least 6 characters long.";
        }
        return true;
    }

    public function register() {
        // Generate unique user ID
        $this->user_id = $this->generateUserId();
        $this->username = $this->user_id; // Set username to user_id for uniqueness

        $query = "INSERT INTO " . $this->table_name . " SET
                  user_id=:user_id,
                  username=:username,
                  first_name=:first_name,
                  last_name=:last_name,
                  email=:email,
                  phone=:phone,
                  password=:password,
                  role=:role,
                  created_at=:created_at";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->role = htmlspecialchars(strip_tags($this->role));

        // Hash password
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

        // Bind parameters
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":created_at", $this->created_at);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login() {
        $query = "SELECT id, user_id, username, first_name, last_name, email, phone, password, role FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->user_id = $row['user_id'];
                $this->username = $row['username'];
                $this->first_name = $row['first_name'];
                $this->last_name = $row['last_name'];
                $this->phone = $row['phone'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    public function userIdExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE user_id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function phoneExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE phone = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->phone);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function readOne() {
        $query = "SELECT id, user_id, username, first_name, last_name, email, phone, role FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->username = $row['username'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }
}
?>