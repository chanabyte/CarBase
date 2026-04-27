<?php
// update_profile.php
include '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_POST['customer_id'];
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    
    try {
        $sql = "UPDATE CUSTOMER SET CustomerName = ?, Email = ? WHERE CustomerID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $customer_id]);
        
        echo "<script>alert('Customer Profile updated successfully!'); window.location.href='../profile.php';</script>";
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: ../profile.php");
}
?>
