<?php
session_start();
include 'config/db_connect.php';

if (!isset($_SESSION['customer_id'])) {
    echo "<script>alert('Please login to view your profile.'); window.location.href='index.php';</script>";
    exit();
}
$customer_id = $_SESSION['customer_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM CUSTOMER WHERE CustomerID = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch();
    
    // Fetch Wishlist Items explicitly required by phase 4
    $stmt = $pdo->prepare("
        SELECT W.VIN, W.WishDate, V.Model, V.Vehicle_Year, V.Price, M.Make, V.Color
        FROM WISHLIST W 
        JOIN VEHICLE V ON W.VIN = V.VIN 
        JOIN MODEL M ON V.Model = M.Model
        WHERE W.CustomerID = ?
    ");
    $stmt->execute([$customer_id]);
    $wishlist = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - CarBase</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-container { max-width: 1200px; margin: 40px auto; display: flex; gap: 40px; padding: 0 20px; }
        .col { flex: 1; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;}
        h2 { border-bottom: 2px solid var(--accent); padding-bottom: 10px; margin-bottom: 20px; color: #333; display: inline-block;}
    </style>
</head>
<body style="background-color: var(--bg-light-secondary); padding-top: 80px;">

    <nav class="navbar" style="background-color: var(--bg-dark); top:0; position:fixed; width: 100%; z-index: 100;">
        <div class="logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <span style="color:white;">CARBASE</span>
        </div>
        <div class="nav-links">
            <a href="index.php" style="color:white;">Home</a>
            <a href="search.php" style="color:white;">Inventory</a>
            <a href="profile.php" style="color:white;">My Account</a>
            <a href="dealer_portal.php" style="color:white;">Dealer Portal</a>
        </div>
    </nav>

    <div class="profile-container">
        <!-- Update Profile -->
        <div class="col" style="flex: 0.8; height: fit-content;">
            <h2>Update Profile</h2>
            <form action="actions/update_profile.php" method="POST">
                <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="font-weight: 600; color: #333; margin-bottom: 5px; display:block; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Full Name</label>
                    <input type="text" name="name" value="" placeholder="new name" required style="width:100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="font-weight: 600; color: #333; margin-bottom: 5px; display:block; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Email Address</label>
                    <input type="email" name="email" value="" placeholder="newemail@gmail.com" required style="width:100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <!-- Additional Required Profile Field if needed -->
                <button type="submit" class="btn-primary" style="width:100%; background: var(--accent); color: white; margin-top:10px;">Save Changes</button>
            </form>
        </div>

        <!-- Wishlist -->
        <div class="col" style="flex: 1.5;">
            <h2>My Wishlist</h2>
            <?php if(count($wishlist) > 0): ?>
                <div style="display:flex; flex-direction:column; gap:15px;">
                    <?php foreach($wishlist as $item): ?>
                        <div style="padding: 15px; border: 1px solid #eee; border-radius: 6px; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <h4 style="font-size: 1.2rem; color: #333;"><?= htmlspecialchars($item['Make'] . ' ' . $item['Model']) ?></h4>
                                <p style="color:#777; font-size:0.9rem;">VIN: <?= htmlspecialchars($item['VIN']) ?> &bull; <?= htmlspecialchars($item['Vehicle_Year']) ?> &bull; Added: <?= htmlspecialchars($item['WishDate']) ?></p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.3rem; font-weight: 800; color: var(--accent);">$<?= number_format($item['Price']) ?></div>
                                <a href="view_vehicle.php?vin=<?= urlencode($item['VIN']) ?>" style="font-size: 0.85rem; color: #4a90e2; text-decoration: underline;">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color:#666;">Your wishlist is completely empty. Start browsing our inventory to save cars here!</p>
                <br>
                <a href="search.php" class="btn-primary" style="background:var(--accent); color:white; border:none; padding:10px 20px;">Browse Inventory</a>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
