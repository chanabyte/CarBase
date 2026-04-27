<?php
include 'config/db_connect.php';

$vin = $_GET['vin'] ?? '';

if(empty($vin)) {
    die("<div style='padding:50px;text-align:center;'><h2>Error: No VIN specified.</h2><a href='index.php'>Go Back</a></div>");
}

$sql = "SELECT v.*, m.Make, my.NumSeats, my.TransType, my.EngineSize, my.FuelType
        FROM VEHICLE v
        JOIN MODEL m ON v.Model = m.Model
        LEFT JOIN MODEL_YEAR my ON v.Model = my.Model AND v.Vehicle_Year = my.Vehicle_Year
        WHERE v.VIN = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$vin]);
$car = $stmt->fetch();

if(!$car) {
    die("<div style='padding:50px;text-align:center;'><h2>Vehicle not found.</h2><a href='index.php'>Go Back</a></div>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($car['Make'] . ' ' . $car['Model']) ?> - CarBase</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .detail-page { max-width: 1000px; margin: 40px auto; padding: 40px; background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .detail-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 2px solid #f4f4f4; padding-bottom: 20px; }
        .detail-title { font-size: 2.5rem; color: var(--text-dark); margin: 0; font-weight: 800; }
        .detail-subtitle { font-size: 1.2rem; color: var(--text-muted); margin-top: 5px; }
        .detail-price { font-size: 3rem; color: var(--accent); font-weight: bold; }
        .grid-info { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .info-box { background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee; transition: transform 0.2s; }
        .info-box:hover { transform: translateY(-3px); border-color: var(--accent); }
        .info-label { font-size: 0.85rem; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; font-weight: 600; }
        .info-value { font-size: 1.2rem; font-weight: 700; color: #333; }
        .color-circle { display: inline-block; width: 18px; height: 18px; border-radius: 50%; border: 1px solid #ccc; margin-right: 8px; vertical-align: middle; }
    </style>
</head>
<body style="background-color: var(--bg-light-secondary); padding-top: 80px;">
    
    <nav class="navbar" style="background-color: var(--bg-dark); top:0; position:fixed; width: 100%; z-index: 100;">
        <div class="logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <span style="color:var(--text-dark);">CARBASE</span>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="dealer_portal.php">Dealer Portal</a>
        </div>
    </nav>

    <div class="detail-page">
        <a href="javascript:history.back()" style="display:inline-block; margin-bottom: 20px; color: var(--accent); text-decoration: none; font-weight: bold;">&larr; Back to Results</a>
        
        <div class="detail-header">
            <div>
                <h1 class="detail-title"><?= htmlspecialchars($car['Make'] . ' ' . $car['Model']) ?></h1>
                <div class="detail-subtitle"><?= htmlspecialchars($car['Vehicle_Year']) ?> &bull; VIN: <?= htmlspecialchars($car['VIN']) ?></div>
            </div>
            <div style="text-align: right;">
                <div class="detail-price">$<?= number_format($car['Price']) ?></div>
                <form action="actions/wishlist_add.php" method="POST" style="margin-top:10px;">
                    <input type="hidden" name="vin" value="<?= htmlspecialchars($car['VIN']) ?>">
                    <button type="submit" style="cursor:pointer; background:var(--bg-dark); color:white; padding:8px 16px; border-radius:4px; border:none; display:inline-flex; align-items:center; gap:8px; font-weight:600;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        Add to Wishlist
                    </button>
                </form>
            </div>
        </div>

        <h3 style="margin-bottom: 20px; color: var(--text-light); font-size: 1.5rem;">Vehicle Specifications</h3>
        <div class="grid-info">
            <div class="info-box">
                <div class="info-label">Color</div>
                <div class="info-value">
                    <span class="color-circle" style="background-color: <?= strtolower(htmlspecialchars($car['Color'])) ?>;"></span>
                    <?= htmlspecialchars($car['Color']) ?>
                </div>
            </div>
            <div class="info-box"><div class="info-label">Mileage</div><div class="info-value"><?= number_format($car['Mileage']) ?> mi</div></div>
            <div class="info-box"><div class="info-label">Condition</div><div class="info-value"><?= $car['NumOwners'] == 0 ? 'New (0 Owners)' : 'Used ('.htmlspecialchars($car['NumOwners']).' Owners)' ?></div></div>
            <div class="info-box"><div class="info-label">Transmission</div><div class="info-value"><?= htmlspecialchars($car['TransType'] ?? 'N/A') ?></div></div>
            <div class="info-box"><div class="info-label">Engine Size</div><div class="info-value"><?= htmlspecialchars($car['EngineSize'] ?? 'N/A') ?> L</div></div>
            <div class="info-box"><div class="info-label">Fuel Type</div><div class="info-value"><?= htmlspecialchars($car['FuelType'] ?? 'N/A') ?></div></div>
            <div class="info-box"><div class="info-label">Seats</div><div class="info-value"><?= htmlspecialchars($car['NumSeats'] ?? 'N/A') ?></div></div>
            <div class="info-box"><div class="info-label">Emissions Class</div><div class="info-value"><?= htmlspecialchars($car['EMC'] ?? 'N/A') ?></div></div>
        </div>
    </div>

</body>
</html>
