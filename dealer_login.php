<?php
session_start();
include 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    if ($action == 'login') {
        $id = $_POST['dealer_id'];
        $stmt = $pdo->prepare("SELECT * FROM DEALERSHIP WHERE DealershipID = ?");
        $stmt->execute([$id]);
        $dealer = $stmt->fetch();
        if ($dealer) {
            $_SESSION['dealership_id'] = $dealer['DealershipID'];
            header("Location: dealer_portal.php");
            exit();
        } else {
            $error = "Dealership ID not found.";
        }
    } 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dealership Sign In | CarBase</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-container { max-width: 450px; margin: 100px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .form-group label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 8px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; outline: none; }
        .form-group input:focus { border-color: var(--accent); }
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
            <a href="search.php">Inventory</a>
        </div>
    </nav>

    <div class="login-container">
        <h2 style="color: #333; margin-bottom: 20px; font-size: 1.8rem;">Dealer Sign In</h2>
        <?php if(isset($error)) echo "<div style='color:red; background:#ffebeb; padding:10px; border-radius:4px; margin-bottom:15px;'>$error</div>"; ?>
        
        <form method="POST">
            <input type="hidden" name="action" value="login">
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Dealership ID</label>
                <input type="number" name="dealer_id" required placeholder="Enter ID (e.g. 1)">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;">Access Portal</button>
        </form>
        
    </div>
</body>
</html>
