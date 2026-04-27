<?php
// process_edit.php
session_start();
include '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $original_vin = $_POST['original_vin'];
    
    // Collect all fields
    $vin = $_POST['vin'] ?? '';
    $make = $_POST['make'] ?? '';
    $model = $_POST['model'] ?? '';
    $year = $_POST['year'] ?? 0;
    $color = $_POST['color'] ?? '';
    $num_owners = empty($_POST['num_owners']) ? 0 : $_POST['num_owners'];
    $price = $_POST['price'] ?? 0;
    $mileage = $_POST['mileage'] ?? 0;
    
    $trans_type = $_POST['trans_type'] ?? 'Auto';
    $num_seats = empty($_POST['num_seats']) ? 4 : $_POST['num_seats'];
    $engine_size = empty($_POST['engine_size']) ? null : $_POST['engine_size'];
    $fuel_type = empty($_POST['fuel_type']) ? null : $_POST['fuel_type'];
    
    $emc = empty($_POST['emc']) ? null : $_POST['emc'];

    try {
        $pdo->beginTransaction();

        // Ensure MODEL exists
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO MODEL (Model, Make) VALUES (?, ?)");
        $stmt->execute([$model, $make]);
        
        // Ensure MODEL_YEAR exists
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO MODEL_YEAR (Model, Vehicle_Year, NumSeats, TransType, EngineSize, FuelType) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$model, $year, $num_seats, $trans_type, $engine_size, $fuel_type]);
        
        // Update shared MODEL_YEAR attributes explicitly
        $stmt = $pdo->prepare("UPDATE MODEL_YEAR SET NumSeats=?, TransType=?, EngineSize=?, FuelType=? WHERE Model=? AND Vehicle_Year=?");
        $stmt->execute([$num_seats, $trans_type, $engine_size, $fuel_type, $model, $year]);

        // Update root VEHICLE attributes exactly as required by spec query Phase 4
        $dealership_id = $_SESSION['dealership_id'] ?? 1;
        $sql = "UPDATE VEHICLE SET Color = ?, NumOwners = ?, Mileage = ?, Price = ?, EMC = ? WHERE VIN = ? AND DealershipID = (SELECT d.DealershipID FROM Dealership d WHERE d.DealershipID = ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$color, $num_owners, $mileage, $price, $emc, $original_vin, $dealership_id]);

        $pdo->commit();
        
        echo "<script>alert('Listing updated successfully!'); window.location.href='../dealer_portal.php';</script>";
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<div style='color: red; padding: 20px; text-align: center; font-family: sans-serif;'>";
        echo "<h2>Database Error During Update</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<a href='../dealer_portal.php'>Return to Portal</a></div>";
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
