
<?php
session_start();
include '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['account_type'] ?? 'CUSTOMER';
    
    try {
        if ($type === 'CUSTOMER') {
            $first = trim($_POST['first_name'] ?? '');
            $last = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            if (empty($first) || empty($last) || empty($email)) {
                echo "<script>alert('Error: First Name, Last Name, and Email are required.'); window.history.back();</script>";
                exit;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<script>alert('Error: Please provide a uniquely valid email format.'); window.history.back();</script>";
                exit;
            }
            
            $name = $first . ' ' . $last;
            
            $stmt = $pdo->query("SELECT MAX(CustomerID) as max_id FROM CUSTOMER");
            $new_id = ($stmt->fetch()['max_id'] ?? 0) + 1;
            
            $stmt = $pdo->prepare("INSERT INTO CUSTOMER (CustomerID, CustomerName, Email) VALUES (?, ?, ?)");
            $stmt->execute([$new_id, $name, $email]);
            
            $_SESSION['customer_id'] = $new_id;
            echo "<script>alert('Customer Profile created successfully! ID: " . $new_id . "'); window.location.href='../index.php';</script>";
            
        } else {
            // DEALERSHIP Route
            $name = trim($_POST['dealer_name'] ?? '');
            $street = trim($_POST['street'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $zip = trim($_POST['zip'] ?? '');
            
            if (empty($name) || empty($street) || empty($city) || empty($zip)) {
                echo "<script>alert('Error: Dealership Name and all Address fields are strictly required.'); window.history.back();</script>";
                exit;
            }
            if (!preg_match('/^[0-9]{5}$/', $zip)) {
                echo "<script>alert('Error: Zipcode must be precisely 5 exactly integers format.'); window.history.back();</script>";
                exit;
            }
            
            $stmt = $pdo->query("SELECT MAX(DealershipID) as m FROM DEALERSHIP");
            $new_id = ($stmt->fetch()['m'] ?? 0) + 1;
            
            $pdo->beginTransaction();
            $pdo->prepare("INSERT OR IGNORE INTO DEALERSHIP_ADDRESS (StreetAddress, City, Zipcode, Finance_Option) VALUES (?, ?, ?, 'Standard Financing')")->execute([$street, $city, $zip]);
            $pdo->prepare("INSERT INTO DEALERSHIP (DealershipID, DealerName, StreetAddress, City, Zipcode) VALUES (?, ?, ?, ?, ?)")->execute([$new_id, $name, $street, $city, $zip]);
            $pdo->commit();
            
            $_SESSION['dealership_id'] = $new_id;
            echo "<script>alert('Dealership registered successfully! ID: " . $new_id . "'); window.location.href='../dealer_portal.php';</script>";
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo "<script>alert('Error creating profile: " . addslashes($e->getMessage()) . "'); window.location.href='../index.php';</script>";
    }
} else {
    header("Location: ../index.php");
}
?>
