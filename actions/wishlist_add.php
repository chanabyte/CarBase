<?php
// wishlist_add.php
session_start();
include '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['customer_id'])) {
        echo "<script>alert('Please login to add vehicles to your wishlist.'); window.location.href='../index.php';</script>";
        exit();
    }
    
    $vin = $_POST['vin'] ?? '';
    // Pull CustomerID from session context instead
    $customer_id = $_SESSION['customer_id'];
    $date = date('Y-m-d');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO WISHLIST (CustomerID, VIN, WishDate) VALUES (?, ?, ?)");
        $stmt->execute([$customer_id, $vin, $date]);
        
        echo "<script>alert('Vehicle securely added to your wishlist!'); window.location.href='../profile.php';</script>";
    } catch (PDOException $e) {
        // If already added, SQLite throws integrity constraint unique
        echo "<script>alert('Vehicle is already currently in your wishlist!'); window.location.href='../profile.php';</script>";
    }
} else {
    header("Location: ../index.php");
}
?>
