<?php
session_start();
include 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($action == 'login_secure') {
        // SECURE: Uses prepared statements
        $stmt = $pdo->prepare("SELECT * FROM DEALERSHIP WHERE Username = ? AND Password = ?");
        $stmt->execute([$username, $password]);
        $dealer = $stmt->fetch();
        
        if ($dealer) {
            $_SESSION['dealership_id'] = $dealer['DealershipID'];
            header("Location: dealer_portal.php");
            exit();
        } else {
            $error_secure = "Invalid credentials (Secure Login).";
        }
    } elseif ($action == 'login_unsecure') {
        // UNSECURE: Vulnerable to SQL Injection
        // We use query() and directly concatenate the string
        $query = "SELECT * FROM DEALERSHIP WHERE Username = '$username' AND Password = '$password'";
        try {
            $result = $pdo->query($query);
            $dealer = $result->fetch();
            
            if ($dealer) {
                $_SESSION['dealership_id'] = $dealer['DealershipID'];
                header("Location: dealer_portal.php");
                exit();
            } else {
                $error_unsecure = "Invalid credentials (Unprotected Login).";
            }
        } catch (PDOException $e) {
            $error_unsecure = "SQL Error: " . $e->getMessage();
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

    <div class="login-container" style="max-width: 900px; display: flex; gap: 0; padding: 0; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: none;">
        <!-- Secure Form -->
        <div style="flex: 1; padding: 50px; background-color: white;">
            <div style="display: flex; align-items: center; margin-bottom: 20px;">
                <span style="font-size: 2rem; margin-right: 15px;">🛡️</span>
                <h2 style="color: #333; margin: 0; font-size: 1.6rem;">Secure Sign In</h2>
            </div>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5;">This form uses <strong>Prepared Statements</strong>. User inputs are treated strictly as data, making it immune to SQL Injection.</p>
            <?php if(isset($error_secure)) echo "<div style='color:#721c24; background:#f8d7da; border: 1px solid #f5c6cb; padding:10px; border-radius:4px; margin-bottom:15px;'>$error_secure</div>"; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="login_secure">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="Enter username">
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter password">
                </div>
                <button type="submit" class="btn-primary" style="width:100%; padding: 14px; background-color: #28a745; border-color: #28a745; font-size: 1.1rem; font-weight: bold; border-radius: 6px; cursor: pointer; box-shadow: 0 4px 6px rgba(40,167,69,0.2);">Protected Login</button>
            </form>
        </div>

        <!-- Unsecure Form -->
        <div style="flex: 1; padding: 50px; background-color: #fdfdfd; border-left: 1px solid #eee;">
            <div style="display: flex; align-items: center; margin-bottom: 20px;">
                <span style="font-size: 2rem; margin-right: 15px;">⚠️</span>
                <h2 style="color: #333; margin: 0; font-size: 1.6rem;">Unsecured Sign In</h2>
            </div>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5;">This form uses <strong>Direct Concatenation</strong>. User inputs are directly added to the query, making it vulnerable.</p>
            <?php if(isset($error_unsecure)) echo "<div style='color:#721c24; background:#f8d7da; border: 1px solid #f5c6cb; padding:10px; border-radius:4px; margin-bottom:15px;'>$error_unsecure</div>"; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="login_unsecure">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="Enter username">
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter password">
                </div>
                <button type="submit" class="btn-primary" style="width:100%; padding: 14px; background-color: #dc3545; border-color: #dc3545; font-size: 1.1rem; font-weight: bold; border-radius: 6px; cursor: pointer; box-shadow: 0 4px 6px rgba(220,53,69,0.2);">Unprotected Login</button>
            </form>
        </div>
    </div>
</body>
</html>
