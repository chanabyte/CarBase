<?php
// process_vehicle.php
session_start();
include '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect all exactly per schema
    $vin = $_POST['vin'] ?? '';
    $make = $_POST['make'] ?? '';
    $model = $_POST['model'] ?? '';
    $year = $_POST['year'] ?? 0;
    $color = $_POST['color'] ?? '';
    $num_owners = empty($_POST['num_owners']) ? 0 : $_POST['num_owners'];
    $price = $_POST['price'] ?? 0;
    $mileage = $_POST['mileage'] ?? 0;
    
    // Model Year details
    $trans_type = $_POST['trans_type'] ?? 'Auto';
    $num_seats = empty($_POST['num_seats']) ? 4 : $_POST['num_seats'];
    $engine_size = empty($_POST['engine_size']) ? null : $_POST['engine_size'];
    $fuel_type = empty($_POST['fuel_type']) ? null : $_POST['fuel_type'];
    
    // EMC
    $emc = empty($_POST['emc']) ? null : $_POST['emc'];
    
    // Strict schema bounds validation
    if ($price < 0 || $mileage < 0 || $year < 1886 || $year > 2100 || $num_owners < 0) {
        echo "<script>alert('Error: Numeric schema constraints violated (Price, Mileage, Owners must be >= 0, Year between 1886-2100).'); window.history.back();</script>";
        exit;
    }
    
    // Consume Dealership ID from Session
    $dealership_id = $_SESSION['dealership_id'] ?? 1;  

    try {
        $pdo->beginTransaction();

        // 1. Ensure MODEL exists
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO MODEL (Model, Make) VALUES (?, ?)");
        $stmt->execute([$model, $make]);
        
        // 2. Ensure MODEL_YEAR exists with all constraints
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO MODEL_YEAR (Model, Vehicle_Year, NumSeats, TransType, EngineSize, FuelType) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$model, $year, $num_seats, $trans_type, $engine_size, $fuel_type]);
        
        // Ensure Dealership 1 exists (fallback for demo purposes)
        $pdo->exec("INSERT OR IGNORE INTO DEALERSHIP (DealershipID, DealerName, StreetAddress, City, Zipcode) VALUES (1, 'Demo Dealership', '123 Main St', 'Dallas', '75001')");

        // 3. Insert into VEHICLE root table matching precise constraints (Phase 4 Spec required Query)
        $sql = "INSERT INTO VEHICLE (VIN, DealershipID, Model, Vehicle_Year, Color, NumOwners, Mileage, Price, EMC) 
                SELECT ?, d.DealershipID, ?, ?, ?, ?, ?, ?, ? 
                FROM Dealership d WHERE d.DealershipID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vin, $model, $year, $color, $num_owners, $mileage, $price, $emc, $dealership_id]);

        // 4. Insert into Subclass
        $vtype = strtoupper($_POST['vehicle_type'] ?? 'CAR');
        if ($vtype === 'CAR') {
            $conv = $_POST['conv_feats'] ?? '';
            $s = $pdo->prepare("INSERT INTO CAR (VIN, FEATURES) VALUES (?, ?)");
            $s->execute([$vin, $conv]);
        } elseif ($vtype === 'TRUCK') {
            $dt = $_POST['drive_train'] ?? 'FWD';
            $tc = $_POST['tow_capacity'] ?? 0;
            $s = $pdo->prepare("INSERT INTO TRUCK (VIN, DriveTrain, TowCapacity) VALUES (?, ?, ?)");
            $s->execute([$vin, $dt, $tc]);
        } elseif ($vtype === 'MOTORCYCLE') {
            $cc = empty($_POST['ccs']) ? 0 : $_POST['ccs'];
            $s = $pdo->prepare("INSERT INTO MOTORCYCLE (VIN, CC) VALUES (?, ?)");
            $s->execute([$vin, $cc]);
        }

        $pdo->commit();
        
        echo "<div style='text-align:center; padding: 50px; font-family: sans-serif;'>";
        echo "<h2 style='color: green;'>Vehicle uploaded successfully! VIN: ".htmlspecialchars($vin)."</h2>";
        echo "<a href='../dealer_portal.php' style='text-decoration:none; padding:10px 20px; background:#FCA311; color: white; border-radius: 5px; font-weight: bold;'>Return to Portal</a>";
        echo "</div>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<div style='color: red; padding: 20px; text-align: center; font-family: sans-serif;'>";
        echo "<h2>Database Error</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<a href='../dealer_portal.php'>Try Again</a></div>";
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
