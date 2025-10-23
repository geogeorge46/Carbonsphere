<?php
session_start();
require_once 'Database.php';
require_once 'User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$message = "";
$errors = [];

if($_POST) {
    // Get form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate first name
    $first_name_validation = $user->validateFirstName($first_name);
    if($first_name_validation !== true) {
        $errors['first_name'] = $first_name_validation;
    }

    // Validate last name
    $last_name_validation = $user->validateLastName($last_name);
    if($last_name_validation !== true) {
        $errors['last_name'] = $last_name_validation;
    }

    // Validate email
    $email_validation = $user->validateEmail($email);
    if($email_validation !== true) {
        $errors['email'] = $email_validation;
    }

    // Validate phone
    $phone_validation = $user->validatePhone($phone);
    if($phone_validation !== true) {
        $errors['phone'] = $phone_validation;
    }

    // Validate password
    $password_validation = $user->validatePassword($password);
    if($password_validation !== true) {
        $errors['password'] = $password_validation;
    }

    // Check password confirmation
    if($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // Check if email already exists
    if(empty($errors['email'])) {
        $user->email = $email;
        if($user->emailExists()) {
            $errors['email'] = "Email already exists.";
        }
    }

    // Check if phone already exists
    if(empty($errors['phone'])) {
        $user->phone = $phone;
        if($user->phoneExists()) {
            $errors['phone'] = "Phone number already exists.";
        }
    }

    // Get role from form submission (default to user)
    $selected_role = isset($_POST['role']) ? $_POST['role'] : 'user';

    // If no errors, proceed with registration
    if(empty($errors)) {
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->email = $email;
        $user->phone = $phone;
        $user->password = $password;
        $user->role = $selected_role;
        $user->created_at = date('Y-m-d H:i:s');

        if($user->register()) {
            $role_text = ucfirst($selected_role);
            $message = "$role_text registration successful! <a href='login.php'>Login here</a>";
        } else {
            $message = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carbonsphere - Registration</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        .success-message {
            background-color: #27ae60;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        .password-strength.weak { color: #e74c3c; }
        .password-strength.medium { color: #f39c12; }
        .password-strength.strong { color: #27ae60; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Carbonsphere</h1>
        <h2>Create Your Account</h2>

        <?php if($message): ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="registerForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                    <?php if(isset($errors['first_name'])): ?>
                        <span class="error-message"><?php echo $errors['first_name']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                    <?php if(isset($errors['last_name'])): ?>
                        <span class="error-message"><?php echo $errors['last_name']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" name="email" id="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                <?php if(isset($errors['email'])): ?>
                    <span class="error-message"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" name="phone" id="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" maxlength="10" required>
                <?php if(isset($errors['phone'])): ?>
                    <span class="error-message"><?php echo $errors['phone']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="role">Account Type:</label>
                <select name="role" id="role" required>
                    <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] === 'user') || !isset($_POST['role']) ? 'selected' : ''; ?>>User Account</option>
                    <option value="seller" <?php echo isset($_POST['role']) && $_POST['role'] === 'seller' ? 'selected' : ''; ?>>Seller Account</option>
                </select>
                <small style="color: #7f8c8d; font-size: 12px;">Choose 'Seller' if you want to sell products on our platform</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                    <div class="password-strength" id="passwordStrength"></div>
                    <?php if(isset($errors['password'])): ?>
                        <span class="error-message"><?php echo $errors['password']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    <?php if(isset($errors['confirm_password'])): ?>
                        <span class="error-message"><?php echo $errors['confirm_password']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit">Create Account</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <script>
        // Real-time validation
        document.getElementById('first_name').addEventListener('input', function() {
            const value = this.value;
            const errorSpan = this.parentElement.querySelector('.error-message');
            if (errorSpan) errorSpan.remove();

            if (value && !/^[a-zA-Z\s]+$/.test(value)) {
                showError(this, 'First name should contain only alphabets and spaces.');
            } else if (value && value.length < 3) {
                showError(this, 'First name should be at least 3 characters long.');
            }
        });

        document.getElementById('last_name').addEventListener('input', function() {
            const value = this.value;
            const errorSpan = this.parentElement.querySelector('.error-message');
            if (errorSpan) errorSpan.remove();

            if (value && !/^[a-zA-Z\s]+$/.test(value)) {
                showError(this, 'Last name should contain only alphabets and spaces.');
            } else if (value && value.length < 3) {
                showError(this, 'Last name should be at least 3 characters long.');
            }
        });

        document.getElementById('phone').addEventListener('input', function() {
            const value = this.value;
            const errorSpan = this.parentElement.querySelector('.error-message');
            if (errorSpan) errorSpan.remove();

            if (value && !/^[6-9][0-9]{0,9}$/.test(value)) {
                showError(this, 'Phone number should start with 6, 7, 8, or 9.');
            } else if (value && value.length !== 10) {
                showError(this, 'Phone number should be exactly 10 digits.');
            }
        });

        document.getElementById('password').addEventListener('input', function() {
            const value = this.value;
            const strengthDiv = document.getElementById('passwordStrength');

            if (value.length < 6) {
                strengthDiv.textContent = 'Password too short';
                strengthDiv.className = 'password-strength weak';
            } else if (value.length < 8) {
                strengthDiv.textContent = 'Weak password';
                strengthDiv.className = 'password-strength weak';
            } else if (/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(value)) {
                strengthDiv.textContent = 'Strong password';
                strengthDiv.className = 'password-strength strong';
            } else {
                strengthDiv.textContent = 'Medium password';
                strengthDiv.className = 'password-strength medium';
            }
        });

        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const errorSpan = this.parentElement.querySelector('.error-message');
            if (errorSpan) errorSpan.remove();

            if (confirmPassword && password !== confirmPassword) {
                showError(this, 'Passwords do not match.');
            }
        });

        function showError(element, message) {
            const errorSpan = document.createElement('span');
            errorSpan.className = 'error-message';
            errorSpan.textContent = message;
            element.parentElement.appendChild(errorSpan);
        }

        // Form submission validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            if (phone && phone.length !== 10) {
                e.preventDefault();
                alert('Phone number must be exactly 10 digits.');
                return false;
            }
        });
    </script>
</body>
</html>