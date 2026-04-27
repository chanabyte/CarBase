<?php
session_start();
include 'config/db_connect.php';

try {
    $makes = $pdo->query("SELECT DISTINCT Make FROM MODEL ORDER BY Make")->fetchAll(PDO::FETCH_COLUMN);
    $models = $pdo->query("SELECT DISTINCT Model FROM MODEL ORDER BY Model")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $makes = [];
    $models = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarBase Phase 5 | Premium Automotive</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background-color: #000000;">

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            CAR<span>BASE</span>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="search.php">Inventory</a>
            <a href="dealer_portal.php">Dealer Portal</a>
            <?php if(isset($_SESSION['customer_id'])): ?>
                <a href="profile.php">My Account</a>
                <a href="actions/logout.php">Logout</a>
            <?php else: ?>
                <button class="btn-primary" onclick="document.getElementById('loginModal').classList.add('active')" style="background:transparent; border:1px solid var(--accent);">Sign In</button>
                <button class="btn-primary" onclick="document.getElementById('signupModal').classList.add('active')">Sign Up</button>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Carbase Automotive</h1>
            <p style="font-size: 1.5rem; color: var(--accent); font-weight: 600;">Inventory Management System</p>
        </div>
        <!-- High-res generated asset -->
        <img src="assets/hero_car.png" alt="Silver Sedan" class="hero-image">
    </section>

    <div class="search-container">
        <form action="search.php" method="GET" class="search-form">
            <div class="form-group">
                <label>Make</label>
                <select name="make">
                    <option value="">Any Make</option>
                    <?php foreach($makes as $m): ?>
                        <option value="<?= htmlspecialchars($m) ?>"><?= htmlspecialchars($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Model</label>
                <select name="model">
                    <option value="">Any Model</option>
                    <?php foreach($models as $mo): ?>
                        <option value="<?= htmlspecialchars($mo) ?>"><?= htmlspecialchars($mo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Condition</label>
                <select name="condition">
                    <option value="">Any Condition</option>
                    <option value="new">New</option>
                    <option value="used">Used</option>
                </select>
            </div>
            <button type="submit" class="btn-search">Search Inventory</button>
        </form>
    </div>



    <!-- Sign Up Modal (Phase 4 Logic preserved) -->
    <div class="modal-overlay" id="signupModal">
        <div class="modal-content">
            <span class="close-modal" onclick="document.getElementById('signupModal').classList.remove('active')">&times;</span>
            <h2>Create Your Profile</h2>
            <p style="color:var(--text-muted); margin-bottom:20px;">Save your favorite vehicles and track listings!</p>
            
            <form action="actions/signup.php" method="POST">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Account Type</label>
                    <select name="account_type" id="accTypeSelect" onchange="toggleSignupFields()" style="width:100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd; outline:none;">
                        <option value="CUSTOMER">Customer Sign Up</option>
                        <option value="DEALERSHIP">Dealership Registration</option>
                    </select>
                </div>
                
                <div id="customer_fields">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone">
                    </div>
                </div>
                
                <div id="dealership_fields" style="display:none;">
                    <div class="form-group">
                        <label>Dealership Name</label>
                        <input type="text" name="dealer_name">
                    </div>
                    <div class="form-group">
                        <label>Street Address</label>
                        <input type="text" name="street">
                    </div>
                    <div style="display:flex; gap:10px;">
                        <div class="form-group" style="flex:1; min-width:0;"><label>City</label><input type="text" name="city"></div>
                        <div class="form-group" style="flex:1; min-width:0;"><label>Zipcode</label><input type="text" name="zip"></div>
                    </div>
                </div>

                <script>
                    function toggleSignupFields() {
                        const type = document.getElementById('accTypeSelect').value;
                        if (type === 'CUSTOMER') {
                            document.getElementById('customer_fields').style.display = 'block';
                            document.getElementById('dealership_fields').style.display = 'none';
                        } else {
                            document.getElementById('customer_fields').style.display = 'none';
                            document.getElementById('dealership_fields').style.display = 'block';
                        }
                    }
                </script>
                <button type="submit" class="btn-search">Sign Up</button>
            </form>
        </div>
    </div>

    <!-- Sign In Modal -->
    <div class="modal-overlay" id="loginModal">
        <div class="modal-content">
            <span class="close-modal" onclick="document.getElementById('loginModal').classList.remove('active')">&times;</span>
            <h2>Sign In</h2>
            <p style="color:var(--text-muted); margin-bottom:20px;">Access your saved searches and wishlist.</p>
            
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter the email you signed up with">
                </div>
                <button type="submit" class="btn-search">Sign In</button>
            </form>
        </div>
    </div>

</body>
</html>