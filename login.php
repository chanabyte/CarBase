<?php
session_start();
include 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $stmt = $pdo->prepare("SELECT CustomerID FROM CUSTOMER WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['customer_id'] = $user['CustomerID'];
        echo "<script>alert('Signed in successfully!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Email not found in our database! Please sign up first.'); window.location.href='index.php';</script>";
    }
} else {
    header("Location: index.php");
}
?>
