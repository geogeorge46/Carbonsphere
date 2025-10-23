<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carbonsphere - Sustainable Living Platform</title>
    <meta name="description" content="Join Carbonsphere - Your gateway to sustainable living. Track carbon footprint, shop eco-friendly products, and connect with like-minded individuals.">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Landing Page Specific Styles */
        .hero {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1.5" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .hero h1 {
            font-size: 3.5em;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero p {
            font-size: 1.3em;
            margin-bottom: 40px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1em;
            transition: all 0.3s ease;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary-hero {
            background: white;
            color: #27ae60;
            border: 2px solid white;
        }

        .btn-primary-hero:hover {
            background: transparent;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .btn-secondary-hero {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary-hero:hover {
            background: white;
            color: #27ae60;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .stats-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3em;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1em;
            color: #666;
            font-weight: 500;
        }

        .features-section {
            padding: 80px 0;
            background: white;
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5em;
            color: #2c3e50;
            margin-bottom: 20px;
            position: relative;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #27ae60, #2ecc71);
            border-radius: 2px;
        }

        .section-title p {
            font-size: 1.2em;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 3em;
            color: #27ae60;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.5em;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        .about-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .about-content h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .about-content p {
            font-size: 1.1em;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .about-image {
            text-align: center;
        }

        .about-image img {
            max-width: 100%;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .cta-section {
            padding: 80px 0;
            background: #27ae60;
            color: white;
            text-align: center;
        }

        .cta-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .cta-container h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .cta-container p {
            font-size: 1.2em;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .testimonial-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .testimonial-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }

        .testimonial {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .testimonial-text {
            font-size: 1.2em;
            color: #666;
            font-style: italic;
            margin-bottom: 20px;
        }

        .testimonial-author {
            font-weight: bold;
            color: #2c3e50;
        }

        .testimonial-role {
            color: #27ae60;
            font-size: 0.9em;
        }

        /* Navigation Styles */
        .main-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            padding: 15px 0;
            transition: all 0.3s ease;
        }

        .main-nav.scrolled {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .nav-container {
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
            color: #27ae60;
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        .nav-menu a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-menu a:hover {
            color: #27ae60;
        }

        .nav-auth {
            display: flex;
            gap: 15px;
        }

        .btn-nav {
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-nav-login {
            color: #27ae60;
            border: 2px solid #27ae60;
        }

        .btn-nav-login:hover {
            background: #27ae60;
            color: white;
        }

        .btn-nav-signup {
            background: #27ae60;
            color: white;
        }

        .btn-nav-signup:hover {
            background: #229954;
            transform: translateY(-2px);
        }

        /* Cart styles */
        .nav-cart {
            position: relative;
            margin-right: 15px;
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

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 60px 0 30px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .footer-section h3 {
            color: #27ae60;
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .footer-section p {
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #27ae60;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            color: #bdc3c7;
            font-size: 1.5em;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: #27ae60;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            margin-top: 40px;
            border-top: 1px solid #34495e;
            color: #95a5a6;
        }

        /* Mobile Menu */
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .mobile-menu-toggle span {
            width: 25px;
            height: 3px;
            background: #2c3e50;
            margin: 3px 0;
            transition: 0.3s;
        }

        .nav-menu.active {
            display: flex;
            flex-direction: column;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 20px 0;
            border-radius: 0 0 10px 10px;
        }

        .nav-menu.active li {
            margin: 10px 0;
            text-align: center;
        }

        .nav-menu.active a {
            padding: 10px 20px;
            display: block;
        }

        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }

        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        /* Animations */
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

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5em;
            }

            .hero p {
                font-size: 1.1em;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .nav-menu {
                display: none;
            }

            .mobile-menu-toggle {
                display: flex;
            }

            .about-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
            }

            .hero {
                padding: 60px 0;
            }

            .hero h1 {
                font-size: 2em;
            }
        }

        /* Services Section */
        .services-section {
            padding: 80px 0;
            background: white;
        }

        .services-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
        }

        .service-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2em;
            color: white;
        }

        .service-card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.4em;
        }

        .service-card p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .service-features {
            list-style: none;
            padding: 0;
            text-align: left;
        }

        .service-features li {
            color: #555;
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
        }

        .service-features li::before {
            content: '✓';
            color: #27ae60;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        /* Pricing Section */
        .pricing-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .pricing-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .pricing-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .pricing-card.featured {
            border: 3px solid #27ae60;
            box-shadow: 0 15px 40px rgba(39, 174, 96, 0.3);
        }

        .pricing-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: #27ae60;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .pricing-header h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .price {
            display: flex;
            align-items: baseline;
            justify-content: center;
            margin-bottom: 30px;
        }

        .currency {
            font-size: 1.5em;
            color: #666;
            margin-right: 5px;
        }

        .amount {
            font-size: 3em;
            font-weight: bold;
            color: #27ae60;
        }

        .period {
            font-size: 1.2em;
            color: #666;
            margin-left: 5px;
        }

        .pricing-features {
            list-style: none;
            padding: 0;
            margin-bottom: 30px;
        }

        .pricing-features li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .pricing-features li i {
            width: 16px;
        }

        .pricing-features li .fa-check {
            color: #27ae60;
        }

        .pricing-features li .fa-times {
            color: #e74c3c;
        }

        .btn-pricing {
            display: inline-block;
            padding: 15px 30px;
            background: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-pricing:hover {
            background: #229954;
            transform: translateY(-2px);
        }

        .btn-pricing.featured {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
        }

        .btn-pricing.featured:hover {
            background: linear-gradient(135deg, #229954, #27ae60);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav" id="mainNav">
        <div class="nav-container">
            <a href="index.php" class="logo">Carbonsphere</a>
            <ul class="nav-menu">
                <li><a href="#home">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#pricing">Pricing</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#testimonials">Reviews</a></li>
            </ul>
            <div class="nav-auth">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="cart.php" class="cart-icon nav-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cartCount">
                            <?php
                            require_once 'Database.php';
                            require_once 'CartClass.php';
                            $database = new Database();
                            $db = $database->getConnection();
                            $cart = new Cart($db);
                            echo $cart->getCartItemCount($_SESSION['user_id']);
                            ?>
                        </span>
                    </a>
                    <a href="<?php echo ($_SESSION['role'] === 'seller') ? 'dashboard.php' : 'user_dashboard.php'; ?>" class="btn-nav btn-nav-login">Dashboard</a>
                    <a href="logout.php" class="btn-nav btn-nav-login">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn-nav btn-nav-login">Login</a>
                    <a href="register.php" class="btn-nav btn-nav-signup">Sign Up</a>
                <?php endif; ?>
            </div>
            <div class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Your Journey to Sustainable Living Starts Here</h1>
            <p>Join thousands of eco-conscious individuals making a difference. Track your carbon footprint, discover sustainable products, and connect with a community that cares about our planet.</p>
            <div class="hero-buttons">
                <a href="register.php" class="btn-hero btn-primary-hero">Get Started Free</a>
                <a href="#features" class="btn-hero btn-secondary-hero">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stat-item fade-in-up">
                <div class="stat-number">10,000+</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-item fade-in-up">
                <div class="stat-number">50,000+</div>
                <div class="stat-label">Products Listed</div>
            </div>
            <div class="stat-item fade-in-up">
                <div class="stat-number">25,000</div>
                <div class="stat-label">Tons CO₂ Saved</div>
            </div>
            <div class="stat-item fade-in-up">
                <div class="stat-number">4.8★</div>
                <div class="stat-label">User Rating</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="features-container">
            <div class="section-title">
                <h2>Why Choose Carbonsphere?</h2>
                <p>Discover the features that make us the leading platform for sustainable living</p>
            </div>
            <div class="features-grid">
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>Carbon Tracking</h3>
                    <p>Monitor your daily carbon footprint with our advanced tracking tools. Get personalized insights and recommendations to reduce your environmental impact.</p>
                </div>
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3>Eco-Friendly Products</h3>
                    <p>Browse thousands of sustainable products from verified eco-friendly sellers. Support businesses that prioritize environmental responsibility.</p>
                </div>
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Community Connection</h3>
                    <p>Connect with like-minded individuals, join environmental challenges, and participate in community events focused on sustainability.</p>
                </div>
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Progress Analytics</h3>
                    <p>Track your sustainability journey with detailed analytics and reports. See your impact grow over time with visual progress indicators.</p>
                </div>
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3>Seller Platform</h3>
                    <p>Start your own eco-friendly business. List products, manage orders, and reach customers who care about sustainability.</p>
                </div>
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Educational Resources</h3>
                    <p>Access comprehensive guides, tutorials, and articles about sustainable living. Learn how to make a positive environmental impact.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="about-container">
            <div class="about-content">
                <h2>About Carbonsphere</h2>
                <p>Carbonsphere was founded with a simple mission: to make sustainable living accessible to everyone. We believe that small changes in individual behavior, when multiplied across millions of people, can create significant positive impact on our planet.</p>
                <p>Our platform combines cutting-edge technology with environmental consciousness to provide tools and resources that empower individuals and businesses to make eco-friendly choices. From tracking carbon footprints to discovering sustainable products, we're here to support your journey toward a greener future.</p>
                <a href="register.php" class="btn-hero btn-secondary-hero">Join Our Mission</a>
            </div>
            <div class="about-image">
                <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300' fill='%2327ae60'><rect width='400' height='300' rx='15'/><circle cx='200' cy='150' r='80' fill='white' opacity='0.2'/><path d='M150 120 Q200 80 250 120 Q280 140 250 160 Q200 180 150 160 Q120 140 150 120' fill='white' opacity='0.3'/></svg>" alt="Sustainable Living" style="max-width: 100%; height: auto;">
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonial-section" id="testimonials">
        <div class="testimonial-container">
            <div class="section-title">
                <h2>What Our Users Say</h2>
                <p>Real stories from real people making a difference</p>
            </div>
            <div class="testimonial fade-in-up">
                <div class="testimonial-text">
                    "Carbonsphere has completely changed how I think about my environmental impact. The carbon tracking feature is incredibly accurate and the product recommendations are spot-on!"
                </div>
                <div class="testimonial-author">
                    Sarah Johnson
                    <div class="testimonial-role">Environmental Consultant</div>
                </div>
            </div>
            <div class="testimonial fade-in-up">
                <div class="testimonial-text">
                    "As a seller, Carbonsphere has helped me reach customers who genuinely care about sustainability. The platform is user-friendly and the community is incredibly supportive."
                </div>
                <div class="testimonial-author">
                    Michael Chen
                    <div class="testimonial-role">Eco-Product Seller</div>
                </div>
            </div>
            <div class="testimonial fade-in-up">
                <div class="testimonial-text">
                    "The educational resources on Carbonsphere are fantastic. I've learned so much about sustainable living, and the community challenges keep me motivated!"
                </div>
                <div class="testimonial-author">
                    Emma Rodriguez
                    <div class="testimonial-role">Student & Activist</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section" id="services">
        <div class="services-container">
            <div class="section-title">
                <h2>Our Services</h2>
                <p>Comprehensive solutions for your sustainable lifestyle journey</p>
            </div>
            <div class="services-grid">
                <div class="service-card fade-in-up">
                    <div class="service-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <h3>Carbon Calculator</h3>
                    <p>Calculate your carbon footprint with our advanced tools. Get detailed breakdowns and personalized reduction strategies.</p>
                    <ul class="service-features">
                        <li>Daily activity tracking</li>
                        <li>Transportation analysis</li>
                        <li>Energy consumption reports</li>
                        <li>Reduction recommendations</li>
                    </ul>
                </div>
                <div class="service-card fade-in-up">
                    <div class="service-icon">
                        <i class="fas fa-store-alt"></i>
                    </div>
                    <h3>Eco Marketplace</h3>
                    <p>Discover and purchase sustainable products from verified eco-friendly sellers worldwide.</p>
                    <ul class="service-features">
                        <li>Verified sustainable products</li>
                        <li>Secure payment processing</li>
                        <li>Fast delivery options</li>
                        <li>Return guarantee</li>
                    </ul>
                </div>
                <div class="service-card fade-in-up">
                    <div class="service-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Seller Partnership</h3>
                    <p>Join our network of eco-conscious sellers. Grow your sustainable business with our platform.</p>
                    <ul class="service-features">
                        <li>Easy product listing</li>
                        <li>Analytics dashboard</li>
                        <li>Marketing support</li>
                        <li>Community features</li>
                    </ul>
                </div>
                <div class="service-card fade-in-up">
                    <div class="service-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Education Hub</h3>
                    <p>Learn about sustainability with our comprehensive educational resources and courses.</p>
                    <ul class="service-features">
                        <li>Interactive courses</li>
                        <li>Expert webinars</li>
                        <li>Resource library</li>
                        <li>Certification programs</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing-section" id="pricing">
        <div class="pricing-container">
            <div class="section-title">
                <h2>Choose Your Plan</h2>
                <p>Start your sustainable journey with flexible pricing options</p>
            </div>
            <div class="pricing-grid">
                <div class="pricing-card fade-in-up">
                    <div class="pricing-header">
                        <h3>Free</h3>
                        <div class="price">
                            <span class="currency">$</span>
                            <span class="amount">0</span>
                            <span class="period">/month</span>
                        </div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Basic carbon tracking</li>
                        <li><i class="fas fa-check"></i> Community access</li>
                        <li><i class="fas fa-check"></i> Educational resources</li>
                        <li><i class="fas fa-times"></i> Premium analytics</li>
                        <li><i class="fas fa-times"></i> Priority support</li>
                    </ul>
                    <a href="register.php" class="btn-pricing">Get Started</a>
                </div>
                <div class="pricing-card featured fade-in-up">
                    <div class="pricing-badge">Most Popular</div>
                    <div class="pricing-header">
                        <h3>Premium</h3>
                        <div class="price">
                            <span class="currency">$</span>
                            <span class="amount">9.99</span>
                            <span class="period">/month</span>
                        </div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Advanced carbon tracking</li>
                        <li><i class="fas fa-check"></i> Premium analytics</li>
                        <li><i class="fas fa-check"></i> Eco-product discounts</li>
                        <li><i class="fas fa-check"></i> Priority support</li>
                        <li><i class="fas fa-check"></i> Exclusive challenges</li>
                    </ul>
                    <a href="register.php" class="btn-pricing featured">Start Premium</a>
                </div>
                <div class="pricing-card fade-in-up">
                    <div class="pricing-header">
                        <h3>Seller</h3>
                        <div class="price">
                            <span class="currency">$</span>
                            <span class="amount">19.99</span>
                            <span class="period">/month</span>
                        </div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Unlimited product listings</li>
                        <li><i class="fas fa-check"></i> Advanced analytics</li>
                        <li><i class="fas fa-check"></i> Marketing tools</li>
                        <li><i class="fas fa-check"></i> 24/7 seller support</li>
                        <li><i class="fas fa-check"></i> Featured listings</li>
                    </ul>
                    <a href="register.php?role=seller" class="btn-pricing">Become a Seller</a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-container">
            <h2>Ready to Make a Difference?</h2>
            <p>Join Carbonsphere today and start your journey toward sustainable living. It's free to get started!</p>
            <a href="register.php" class="btn-hero btn-primary-hero">Create Your Account</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>Carbonsphere</h3>
                <p>Your gateway to sustainable living. Together, we can make a difference for our planet.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Platform</h3>
                <ul class="footer-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="register.php?role=seller">Become a Seller</a></li>
                    <li><a href="#">Pricing</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Support</h3>
                <ul class="footer-links">
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Resources</h3>
                <ul class="footer-links">
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Sustainability Guide</a></li>
                    <li><a href="#">API Documentation</a></li>
                    <li><a href="#">Community</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Carbonsphere. All rights reserved. | Made with <i class="fas fa-heart" style="color: #e74c3c;"></i> for the planet</p>
        </div>
    </footer>

    <script>
        // Navigation scroll effect
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('mainNav');
            if (window.scrollY > 100) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Mobile menu toggle
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const navMenu = document.querySelector('.nav-menu');

        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
        });

        // Animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, observerOptions);

        // Observe all elements that should animate
        document.querySelectorAll('.feature-card, .stat-item, .testimonial').forEach(el => {
            observer.observe(el);
        });

        // Add fade-in-up class to elements initially visible
        document.addEventListener('DOMContentLoaded', function() {
            const visibleElements = document.querySelectorAll('.feature-card, .stat-item, .testimonial');
            visibleElements.forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    el.classList.add('fade-in-up');
                }
            });
        });

        // Show cart function
        function showCart() {
            // For now, redirect to products page
            window.location.href = 'products.php';
        }
    </script>
    <script src="product.js"></script>
</body>
</html>