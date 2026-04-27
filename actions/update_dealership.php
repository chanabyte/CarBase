<?php
// update_dealership.php
session_start();
include '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dealer_name = $_POST['dealer_name'] ?? '';
    $street = $_POST['street_address'] ?? '';
    $city = $_POST['city'] ?? '';
    $zip = $_POST['zip'] ?? '';
    
    // Select logged in dealership ID
    $dealership_id = $_SESSION['dealership_id'] ?? 1;

    try {
        $pdo->beginTransaction();
        
        // Ensure Dealership Address exists
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO DEALERSHIP_ADDRESS (StreetAddress, City, Zipcode, Finance_Option) VALUES (?, ?, ?, 'Standard Financing')");
        $stmt->execute([$street, $city, $zip]);

        // Enforce Phase 4 Query
        $sql = "UPDATE Dealership SET DealerName = ?, StreetAddress = ?, City = ?, Zipcode = ? WHERE DealershipID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dealer_name, $street, $city, $zip, $dealership_id]);

        $pdo->commit();
        echo "<script>alert('Profile updated fully compliance!'); window.location.href='../dealer_portal.php';</script>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Error: " . htmlspecialchars($e->getMessage());
    }
} else {
    header("Location: ../dealer_portal.php");
}
?>
