<?php
// edit_vehicle.php
include 'config/db_connect.php';

$vin = $_GET['vin'] ?? '';
if (empty($vin)) {
    die("No VIN specified for editing.");
}

$sql = "SELECT v.*, m.Make, my.NumSeats, my.TransType, my.EngineSize, my.FuelType
        FROM VEHICLE v
        JOIN MODEL m ON v.Model = m.Model
        LEFT JOIN MODEL_YEAR my ON v.Model = my.Model AND v.Vehicle_Year = my.Vehicle_Year
        WHERE v.VIN = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$vin]);
$car = $stmt->fetch();

if (!$car) {
    die("Vehicle not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Listing - CarBase</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background-color: var(--bg-light-secondary); padding-top: 100px;">
    
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

    <div style="max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <h2 style="margin-bottom: 20px; font-size: 2rem; color: var(--text-dark);">Edit Vehicle: <?= htmlspecialchars($vin) ?></h2>
        <a href="dealer_portal.php" style="color: var(--accent); text-decoration: none; display: inline-block; margin-bottom: 20px; font-weight: bold;">&larr; Back to Portal</a>
        
        <form action="actions/process_edit.php" method="POST">
            <input type="hidden" name="original_vin" value="<?= htmlspecialchars($vin) ?>">
            
            <!-- VEHICLE CORE -->
            <div class="form-row">
                <div class="form-group">
                    <label>VIN (17 chars) *</label>
                    <input type="text" name="vin" maxlength="17" value="<?= htmlspecialchars($car['VIN']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Color *</label>
                    <input type="text" name="color" value="<?= htmlspecialchars($car['Color']) ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Make *</label>
                    <input type="text" name="make" value="<?= htmlspecialchars($car['Make']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Model *</label>
                    <input type="text" name="model" value="<?= htmlspecialchars($car['Model']) ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Year *</label>
                    <input type="number" name="year" value="<?= htmlspecialchars($car['Vehicle_Year']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Number of Owners</label>
                    <input type="number" name="num_owners" value="<?= htmlspecialchars($car['NumOwners']) ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Price ($) *</label>
                    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($car['Price']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Mileage *</label>
                    <input type="number" name="mileage" value="<?= htmlspecialchars($car['Mileage']) ?>" required>
                </div>
            </div>
            
            <!-- MODEL_YEAR SPECS -->
            <h3 style="margin-top: 10px; margin-bottom: 10px; font-size: 1rem;">Model Specifications</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>TransType</label>
                    <select name="trans_type">
                        <option value="Auto" <?= ($car['TransType'] == 'Auto') ? 'selected' : '' ?>>Auto</option>
                        <option value="Manual" <?= ($car['TransType'] == 'Manual') ? 'selected' : '' ?>>Manual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Number of Seats</label>
                    <input type="number" name="num_seats" value="<?= htmlspecialchars($car['NumSeats'] ?? 4) ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Engine Size (L)</label>
                    <input type="number" step="0.1" name="engine_size" value="<?= htmlspecialchars($car['EngineSize']) ?>">
                </div>
                <div class="form-group">
                    <label>Fuel Type</label>
                    <select name="fuel_type">
                        <option value="Gas" <?= ($car['FuelType'] == 'Gas' || $car['FuelType'] == 'Gasoline') ? 'selected' : '' ?>>Gasoline</option>
                        <option value="Electric" <?= ($car['FuelType'] == 'Electric') ? 'selected' : '' ?>>Electric</option>
                        <option value="Hybrid" <?= ($car['FuelType'] == 'Hybrid') ? 'selected' : '' ?>>Hybrid</option>
                        <option value="Diesel" <?= ($car['FuelType'] == 'Diesel') ? 'selected' : '' ?>>Diesel</option>
                    </select>
                </div>
            </div>
            
            <!-- MISC -->
            <div class="form-group" style="margin-top: 10px;">
                <label>EMC (Emissions Class)</label>
                <input type="number" step="0.01" name="emc" value="<?= htmlspecialchars($car['EMC']) ?>">
            </div>

            <button type="submit" class="btn-search" style="margin-top:20px; width:100%;">Save Changes</button>
        </form>
    </div>
</body>
</html>
