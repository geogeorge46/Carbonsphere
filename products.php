<?php
session_start();
require_once 'Database.php';
require_once 'Product.php';
require_once 'CartClass.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$cart = new Cart($db);

// Get filters from URL parameters
$filters = [];
if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
if (isset($_GET['category'])) $filters['category'] = $_GET['category'];
if (isset($_GET['min_price'])) $filters['min_price'] = $_GET['min_price'];
if (isset($_GET['max_price'])) $filters['max_price'] = $_GET['max_price'];

// Get products
$products = $product->readAll($filters);

// Get categories for filter
$categories_stmt = $product->getCategories();
$categories = [];
while($cat = $categories_stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $cat;
}

// Get cart count if user is logged in
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $cartCount = $cart->getCartItemCount($_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carbonsphere - Products</title>
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

        .btn-nav-signup {
            background: transparent;
            color: #27ae60;
            border: 2px solid #27ae60;
        }

        .btn-nav-signup:hover {
            background: #27ae60;
            color: white;
        }

        /* Product Page Styles */
        .products-header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .products-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .filters-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 40px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #27ae60;
        }

        .search-box button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #27ae60;
            font-size: 18px;
            cursor: pointer;
        }

        .filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-group select,
        .filter-group input {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .filter-group input[type="number"] {
            width: 100px;
        }

        .btn-filter {
            padding: 10px 20px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .btn-filter:hover {
            background: #229954;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-image .no-image {
            color: #666;
            font-size: 48px;
        }

        .product-info {
            padding: 20px;
        }

        .product-name {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
            text-decoration: none;
        }

        .product-price {
            font-size: 20px;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 10px;
        }

        .product-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .product-category {
            background: #e8f5e8;
            color: #27ae60;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .product-stock {
            font-size: 12px;
            color: #666;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .btn-add-cart {
            flex: 1;
            padding: 10px 15px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-add-cart:hover {
            background: #229954;
        }

        .btn-add-cart:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .btn-view-details {
            padding: 10px 15px;
            background: transparent;
            color: #27ae60;
            border: 2px solid #27ae60;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-view-details:hover {
            background: #27ae60;
            color: white;
        }

        .login-prompt {
            text-align: center;
            padding: 20px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .login-prompt a {
            color: #27ae60;
            font-weight: 500;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #27ae60;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            max-width: 300px;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.error {
            background: #e74c3c;
        }

        /* Navigation with cart */
        .nav-cart {
            position: relative;
            margin-left: 20px;
        }

        .cart-icon {
            position: relative;
            color: #2c3e50;
            text-decoration: none;
            font-size: 20px;
            transition: color 0.3s ease;
        }

        .cart-icon:hover {
            color: #27ae60;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        /* Cart Modal */
        .cart-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: flex-end;
            align-items: flex-start;
        }

        .cart-modal.show {
            display: flex;
        }

        .cart-modal-content {
            background: white;
            width: 400px;
            max-width: 90vw;
            height: 100vh;
            box-shadow: -5px 0 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        .cart-modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-modal-header h3 {
            margin: 0;
            color: #2c3e50;
        }

        .cart-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-close:hover {
            color: #e74c3c;
        }

        .cart-modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 0;
        }

        .cart-loading {
            padding: 40px;
            text-align: center;
            color: #666;
        }

        .cart-empty {
            padding: 40px 20px;
            text-align: center;
        }

        .cart-empty i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }

        .cart-empty p {
            color: #666;
            margin-bottom: 20px;
        }

        .cart-item-modal {
            display: flex;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .cart-item-modal:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-right: 15px;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .cart-item-price {
            color: #27ae60;
            font-size: 14px;
            font-weight: 500;
        }

        .cart-item-remove {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 8px;
            cursor: pointer;
            font-size: 12px;
        }

        .cart-item-remove:hover {
            background: #c0392b;
        }

        .cart-footer {
            border-top: 1px solid #eee;
            padding: 20px;
        }

        .cart-total {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: 600;
            color: #27ae60;
            margin-bottom: 15px;
        }

        .cart-checkout-btn {
            width: 100%;
            padding: 12px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background 0.3s ease;
        }

        .cart-checkout-btn:hover {
            background: #229954;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .filters-section {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                justify-content: center;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }

            .cart-modal-content {
                width: 100%;
                max-width: 100vw;
            }
        }

        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }

            .product-actions {
                flex-direction: column;
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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="cart.php" class="cart-icon nav-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cartCount"><?php echo $cartCount; ?></span>
                    </a>
                    <a href="logout.php" class="btn-nav btn-nav-login">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn-nav btn-nav-login">Login</a>
                    <a href="register.php" class="btn-nav btn-nav-signup">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="products-header">
        <div class="products-container">
            <h1>Discover Sustainable Products</h1>
            <p>Shop eco-friendly products from verified sellers committed to sustainability</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="products-container">
        <!-- Filters -->
        <div class="filters-section">
            <div class="search-box">
                <form method="GET" action="products.php">
                    <input type="text" name="search" placeholder="Search products..."
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="filter-group">
                <select name="category" id="categoryFilter">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['name']); ?>"
                                <?php echo (isset($_GET['category']) && $_GET['category'] == $category['name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="number" name="min_price" id="minPriceFilter" placeholder="Min Price"
                       value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">

                <input type="number" name="max_price" id="maxPriceFilter" placeholder="Max Price"
                       value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">

                <button type="button" class="btn-filter" onclick="applyFilters()">Apply Filters</button>
                <button type="button" class="btn-filter" onclick="clearFilters()">Clear Filters</button>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="products-grid" id="productsGrid">
            <?php
            $productCount = 0;
            while($row = $products->fetch(PDO::FETCH_ASSOC)):
                $productCount++;
            ?>
                <div class="product-card" onclick="viewProduct(<?php echo $row['id']; ?>)">
                    <div class="product-image">
                        <?php if($row['image']): ?>
                            <img src="<?php echo htmlspecialchars($row['image']); ?>"
                                 alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <?php else: ?>
                            <div class="no-image"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="product-name">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </a>

                        <div class="product-price">$<?php echo number_format($row['price'], 2); ?></div>

                        <div class="product-description">
                            <?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...
                        </div>

                        <div class="product-meta">
                            <span class="product-category"><?php echo htmlspecialchars($row['category_name']); ?></span>
                            <span class="product-stock">
                                <?php echo $row['stock_quantity'] > 0 ? $row['stock_quantity'] . ' in stock' : 'Out of stock'; ?>
                            </span>
                        </div>

                        <div class="product-actions">
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <button class="btn-add-cart"
                                        onclick="event.stopPropagation(); addToCart(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['name'])); ?>')"
                                        <?php echo $row['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            <?php else: ?>
                                <div class="login-prompt">
                                    <a href="login.php">Login to Buy</a>
                                </div>
                            <?php endif; ?>

                            <a href="product_detail.php?id=<?php echo $row['id']; ?>"
                               class="btn-view-details"
                               onclick="event.stopPropagation()">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if($productCount == 0): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                    <h3>No products found</h3>
                    <p>Try adjusting your search criteria or browse all products.</p>
                    <a href="products.php" class="btn-filter" style="margin-top: 20px;">View All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cart Modal -->
    <div id="cartModal" class="cart-modal">
        <div class="cart-modal-content">
            <div class="cart-modal-header">
                <h3>Shopping Cart</h3>
                <button class="cart-close" onclick="closeCartModal()">&times;</button>
            </div>
            <div class="cart-modal-body" id="cartModalBody">
                <div class="cart-loading">Loading cart...</div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script src="product.js"></script>
    <script>
        // Apply filters function
        function applyFilters() {
            const category = document.getElementById('categoryFilter').value;
            const minPrice = document.getElementById('minPriceFilter').value;
            const maxPrice = document.getElementById('maxPriceFilter').value;
            const search = document.querySelector('input[name="search"]').value;

            let url = 'products.php?';
            if (search) url += 'search=' + encodeURIComponent(search) + '&';
            if (category) url += 'category=' + encodeURIComponent(category) + '&';
            if (minPrice) url += 'min_price=' + encodeURIComponent(minPrice) + '&';
            if (maxPrice) url += 'max_price=' + encodeURIComponent(maxPrice) + '&';

            window.location.href = url.slice(0, -1); // Remove trailing &
        }

        // Clear filters
        function clearFilters() {
            document.querySelector('input[name="search"]').value = '';
            document.getElementById('categoryFilter').value = '';
            document.getElementById('minPriceFilter').value = '';
            document.getElementById('maxPriceFilter').value = '';
            window.location.href = 'products.php';
        }

        // View product (redirect to detail page)
        function viewProduct(productId) {
            window.location.href = 'product_detail.php?id=' + productId;
        }

        // Show cart modal
        function showCart() {
            const modal = document.getElementById('cartModal');
            const modalBody = document.getElementById('cartModalBody');

            modalBody.innerHTML = '<div class="cart-loading">Loading cart...</div>';
            modal.classList.add('show');

            // Load cart items
            loadCartModal();
        }

        // Close cart modal
        function closeCartModal() {
            const modal = document.getElementById('cartModal');
            modal.classList.remove('show');
        }

        // Load cart items into modal
        async function loadCartModal() {
            try {
                const response = await fetch('CartController.php?action=get_items');
                const result = await response.json();

                const modalBody = document.getElementById('cartModalBody');

                if (result.success && result.items.length > 0) {
                    let html = '';

                    result.items.forEach(item => {
                        // Handle missing product data
                        item.name = item.name || 'Product not found';
                        item.price = item.price || 0;
                        item.image = item.image || null;

                        html += `
                            <div class="cart-item-modal">
                                <div class="cart-item-image">
                                    ${item.image ? `<img src="${item.image}" alt="${item.name}">` : '<i class="fas fa-image"></i>'}
                                </div>
                                <div class="cart-item-details">
                                    <div class="cart-item-name">${item.name}</div>
                                    <div class="cart-item-price">$${item.price} × ${item.quantity} = $${(item.price * item.quantity).toFixed(2)}</div>
                                </div>
                                <button class="cart-item-remove" onclick="removeFromCart(${item.cart_id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    });

                    // Calculate total
                    const total = result.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);

                    html += `
                        <div class="cart-footer">
                            <div class="cart-total">
                                <span>Total:</span>
                                <span>$${total.toFixed(2)}</span>
                            </div>
                            <a href="cart.php" class="cart-checkout-btn">View Cart & Checkout</a>
                        </div>
                    `;

                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = `
                        <div class="cart-empty">
                            <i class="fas fa-shopping-cart"></i>
                            <p>Your cart is empty</p>
                            <a href="products.php" class="cart-checkout-btn" style="margin-top: 15px;">Continue Shopping</a>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading cart:', error);
                const modalBody = document.getElementById('cartModalBody');
                modalBody.innerHTML = '<div class="cart-loading">Error loading cart</div>';
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('cartModal');
            if (event.target === modal) {
                closeCartModal();
            }
        });

    </script>
</body>
</html>