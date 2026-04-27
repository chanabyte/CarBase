<?php
// dealer_portal.php
session_start();
if (!isset($_SESSION['dealership_id'])) {
    header("Location: dealer_login.php");
    exit();
}
include 'config/db_connect.php';

// Fetch manage listings identical to manage_listings.php logic to render below upload
$dealership_id = $_SESSION['dealership_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM VEHICLE WHERE VIN = ?");
        $stmt->execute([$_POST['vin']]);
    } elseif ($_POST['action'] == 'update') {
        $stmt = $pdo->prepare("UPDATE VEHICLE SET Price = ? WHERE VIN = ?");
        $stmt->execute([$_POST['price'], $_POST['vin']]);
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM VEHICLE WHERE DealershipID = ?");
    $stmt->execute([$dealership_id]);
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) { $vehicles = []; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dealer Portal | CarBase</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background-color: var(--bg-light-secondary); padding: 40px; color: var(--text-light); }
        .dealer-container { max-width: 1000px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h1 { color: var(--accent); margin-bottom: 30px; }
        .grid-forms { display: grid; grid-template-columns: 1fr; gap: 40px; }
        .form-section { background: var(--bg-light-secondary); padding: 30px; border-radius: 8px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px;}
        .car-row { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; padding: 15px 0;}
    </style>
</head>
<body>

    <a href="actions/logout.php" style="display:inline-block; margin-bottom:20px; font-weight:bold; color:var(--accent); padding:10px 15px; border:1px solid var(--accent); border-radius:4px; text-decoration:none;">&#x2192; Exit Portal (Logout)</a>

    <div class="dealer-container">
        <h1>Dealership Control Center</h1>
        
        <div class="grid-forms">
            <!-- Upload Vehicle Form (Migrated from original mainpage) -->
            <div class="form-section">
                <h2>Upload New Vehicle</h2>
                <br>
                <form action="actions/process_vehicle.php" method="POST">
                    <!-- VEHICLE CORE -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>VIN (17 chars) *</label>
                            <input type="text" name="vin" maxlength="17" required>
                        </div>
                        <div class="form-group">
                            <label>Color *</label>
                            <input type="text" name="color" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Make *</label>
                            <input type="text" name="make" required>
                        </div>
                        <div class="form-group">
                            <label>Model *</label>
                            <input type="text" name="model" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Year *</label>
                            <input type="number" name="year" required>
                        </div>
                        <div class="form-group">
                            <label>Number of Owners</label>
                            <input type="number" name="num_owners" value="0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Price ($) *</label>
                            <input type="number" step="0.01" name="price" required>
                        </div>
                        <div class="form-group">
                            <label>Mileage *</label>
                            <input type="number" name="mileage" required>
                        </div>
                    </div>
                    
                    <!-- MODEL_YEAR SPECS -->
                    <h3 style="margin-top: 10px; margin-bottom: 10px; font-size: 1rem;">Model Specifications</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>TransType</label>
                            <select name="trans_type">
                                <option value="Auto">Auto</option>
                                <option value="Manual">Manual</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Number of Seats</label>
                            <input type="number" name="num_seats" value="4" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Engine Size (L)</label>
                            <input type="number" step="0.1" name="engine_size">
                        </div>
                        <div class="form-group">
                            <label>Fuel Type</label>
                            <select name="fuel_type">
                                <option value="Gas">Gasoline</option>
                                <option value="Electric">Electric</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="Diesel">Diesel</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- SUBCLASS SELECTION -->
                    <h3 style="margin-top: 10px; margin-bottom: 10px; font-size: 1rem;">Vehicle Subclass Details</h3>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Vehicle Type</label>
                        <select name="vehicle_type" id="vTypeSelect" onchange="toggleSubclassFields()" style="-webkit-appearance: none; appearance: none; background: url('data:image/svg+xml;utf8,<svg fill=%22%23333%22 height=%2224%22 viewBox=%220 0 24 24%22 width=%2224%22 xmlns=%22http://www.w3.org/2000/svg%22><path d=%22M7 10l5 5 5-5z%22/></svg>') no-repeat right 10px center; padding-right: 35px;">
                            <option value="CAR">Car</option>
                            <option value="TRUCK">Truck</option>
                            <option value="MOTORCYCLE">Motorcycle</option>
                        </select>
                    </div>
                
                    <div id="car_fields" class="subclass-fields" style="display:block; padding: 15px; background: #fdfdfd; border: 1px solid #eee; border-radius: 6px;">
                        <div class="form-group">
                            <label>Convenience Features (CAR)</label>
                            <input type="text" name="conv_feats" placeholder="e.g. Heated Seats, Sunroof">
                        </div>
                    </div>
                    
                    <div id="truck_fields" class="subclass-fields" style="display:none; padding: 15px; background: #fdfdfd; border: 1px solid #eee; border-radius: 6px;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Drive Train</label>
                                <select name="drive_train">
                                    <option value="4WD">4WD</option>
                                    <option value="AWD">AWD</option>
                                    <option value="FWD">FWD</option>
                                    <option value="RWD">RWD</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tow Capacity (lbs)</label>
                                <input type="number" name="tow_capacity">
                            </div>
                        </div>
                    </div>
                    
                    <div id="motorcycle_fields" class="subclass-fields" style="display:none; padding: 15px; background: #fdfdfd; border: 1px solid #eee; border-radius: 6px;">
                        <div class="form-group">
                            <label>Engine CC (MOTORCYCLE)</label>
                            <input type="number" name="ccs" placeholder="e.g. 600">
                        </div>
                    </div>
                    
                    <!-- MISC -->
                    <div class="form-group" style="margin-top: 15px;">
                        <label>EMC (Estimated Maintenance Cost)</label>
                        <input type="number" step="0.01" name="emc">
                    </div>

                    <button type="submit" class="btn-search" style="margin-top:20px; width:100%;">Upload Vehicle</button>
                    
                    <script>
                        function toggleSubclassFields() {
                            document.querySelectorAll('.subclass-fields').forEach(el => el.style.display = 'none');
                            const val = document.getElementById('vTypeSelect').value.toLowerCase();
                            document.getElementById(val + '_fields').style.display = 'block';
                        }
                    </script>
                </form>
            </div>
            
            <!-- Second Column: Profile & Inventory -->
            <div style="display: flex; flex-direction: column; gap: 30px;">
                
                <!-- Dealership Profile Update Block -->
                <div class="portal-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <h2 style="margin-bottom: 20px; font-size: 1.5rem; color: var(--text-dark);">Dealership Profile</h2>
                    <form action="actions/update_dealership.php" method="POST">
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Dealer Name</label>
                            <input type="text" name="dealer_name" placeholder="Name" required>
                        </div>
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Street Address</label>
                            <input type="text" name="street_address" placeholder="Address" required>
                        </div>
                        <div class="form-row" style="margin-bottom:10px;">
                            <div class="form-group"><label>City</label><input type="text" name="city" required></div>
                            <div class="form-group"><label>Zipcode</label><input type="text" name="zip" required></div>
                        </div>
                        <button type="submit" class="btn-search" style="width:100%;">Update Profile</button>
                    </form>
                </div>

            <!-- Manage Listings combined into Dealer portal -->
            <div class="form-section" style="background: white; border: 2px solid var(--bg-light-secondary);">
                <h2>Manage My Listings</h2>
                <br>
                <?php if(count($vehicles) > 0): ?>
                    <?php foreach($vehicles as $car): ?>
                        <div class="car-row">
                            <div>
                                <h3 style="margin-bottom:5px;"><?= htmlspecialchars($car['Model']) ?> (<?= $car['Vehicle_Year'] ?>)</h3>
                                <p style="color:#666; font-size:0.9rem;">VIN: <?= $car['VIN'] ?> | Mileage: <?= $car['Mileage'] ?></p>
                            </div>
                            <div style="display:flex; gap:10px; align-items: center;">
                                <a href="edit_vehicle.php?vin=<?= urlencode($car['VIN']) ?>" style="background:var(--accent); color:var(--bg-dark); text-decoration:none; padding:10px 15px; border-radius:4px; font-weight:bold; cursor:pointer;">Edit Complete Listing</a>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="vin" value="<?= $car['VIN'] ?>">
                                    <button type="submit" style="background:#f44336; color:white; border:none; padding:10px 15px; border-radius:4px; font-weight:bold; cursor:pointer;">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No listings active.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</body>
</html>
