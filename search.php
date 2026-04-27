<?php
// search.php
include 'config/db_connect.php';

try {
    $makes = $pdo->query("SELECT DISTINCT Make FROM MODEL ORDER BY Make")->fetchAll(PDO::FETCH_COLUMN);
    $models = $pdo->query("SELECT DISTINCT Model FROM MODEL ORDER BY Model")->fetchAll(PDO::FETCH_COLUMN);
    $colors = $pdo->query("SELECT DISTINCT Color FROM VEHICLE ORDER BY Color")->fetchAll(PDO::FETCH_COLUMN);
    $fuelTypes = $pdo->query("SELECT DISTINCT FuelType FROM MODEL_YEAR WHERE FuelType IS NOT NULL ORDER BY FuelType")->fetchAll(PDO::FETCH_COLUMN);
    $transTypes = $pdo->query("SELECT DISTINCT TransType FROM MODEL_YEAR WHERE TransType IS NOT NULL ORDER BY TransType")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $makes = []; $models = []; $colors = []; $fuelTypes = []; $transTypes = [];
}

$make = $_GET['make'] ?? '';
$model = $_GET['model'] ?? '';
$color = $_GET['color'] ?? '';
$fuel = $_GET['fuel'] ?? '';
$trans = $_GET['trans'] ?? '';
$condition = $_GET['condition'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Start building SQL query
$sql = "SELECT v.*, m.Make FROM VEHICLE v 
        JOIN MODEL m ON v.Model = m.Model 
        LEFT JOIN MODEL_YEAR my ON v.Model = my.Model AND v.Vehicle_Year = my.Vehicle_Year
        WHERE 1=1";
$params = [];

if (!empty($make)) {
    $makesList = array_filter(array_map('trim', explode(',', $make)));
    if (count($makesList) > 0) {
        $placeholders = implode(',', array_fill(0, count($makesList), '?'));
        $sql .= " AND m.Make IN ($placeholders)";
        foreach($makesList as $m) { $params[] = $m; }
    }
}
if (!empty($model)) {
    $modelsList = array_filter(array_map('trim', explode(',', $model)));
    if (count($modelsList) > 0) {
        $placeholders = implode(',', array_fill(0, count($modelsList), '?'));
        $sql .= " AND v.Model IN ($placeholders)";
        foreach($modelsList as $mo) { $params[] = $mo; }
    }
}
if (!empty($color)) {
    $sql .= " AND v.Color = ?";
    $params[] = $color;
}
if (!empty($fuel)) {
    $sql .= " AND my.FuelType = ?";
    $params[] = $fuel;
}
if (!empty($trans)) {
    $sql .= " AND my.TransType = ?";
    $params[] = $trans;
}
if (!empty($max_price)) {
    $sql .= " AND v.Price <= ?";
    $params[] = $max_price;
}
if (!empty($condition)) {
    if ($condition == 'new') {
        $sql .= " AND v.NumOwners = 0";
    } elseif ($condition == 'used') {
        $sql .= " AND v.NumOwners > 0";
    }
}

$sql .= " ORDER BY RANDOM()";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error executing search: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory - CarBase</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .search-layout { display: flex; flex-direction: column; max-width: 1300px; margin: 40px auto; gap: 30px; padding: 0 20px;}
        .top-filter-bar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); padding: 18px 25px; border-radius: 8px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); margin-bottom: 15px; border: 1px solid #eaeaea;}
        .filter-form { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 110px; }
        .filter-group label { display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 5px; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px;}
        .filter-group input, .filter-group select { width: 100%; padding: 10px 14px; font-size: 0.95rem; border: 2px solid #ededed; border-radius: 6px; background-color: #f9f9f9; outline:none; transition: all 0.3s ease; }
        .filter-group input:focus, .filter-group select:focus { border-color: var(--accent); background-color: #ffffff; box-shadow: 0 0 0 4px rgba(252, 163, 17, 0.15); }
        .filter-group select { -webkit-appearance: none; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg fill="%23333333" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>'); background-repeat: no-repeat; background-position: right 10px center; padding-right: 30px; }
        .filter-actions { display: flex; gap: 10px; flex: 1; min-width: 200px; align-items: stretch; }
        .btn-apply { background: var(--accent); color: var(--bg-dark); font-weight: 800; padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer; flex-grow: 1; transition: background 0.2s; font-size: 0.95rem; letter-spacing: 0.5px;}
        .btn-apply:hover { background: #e0920f; }
        .clear-filters { text-align: center; display: flex; justify-content: center; align-items: center; padding: 10px 18px; font-size: 0.95rem; color: #555; background: #eee; border-radius: 6px; text-decoration: none; font-weight: 700; transition: background 0.2s;}
        .clear-filters:hover { background: #e0e0e0; }
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
            <a href="dealer_portal.php">Dealer Portal</a>
        </div>
    </nav>

    <div class="search-layout">
        
        <!-- Top Floating Filter Bar -->
        <div class="top-filter-bar">
            <form action="search.php" method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Make</label>
                    <select name="make">
                        <option value="">Any</option>
                        <?php foreach($makes as $m): ?>
                            <option value="<?= htmlspecialchars($m) ?>" <?= $make === $m ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Model</label>
                    <select name="model">
                        <option value="">Any</option>
                        <?php foreach($models as $mo): ?>
                            <option value="<?= htmlspecialchars($mo) ?>" <?= $model === $mo ? 'selected' : '' ?>><?= htmlspecialchars($mo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Condition</label>
                    <select name="condition">
                        <option value="">Any</option>
                        <option value="new" <?= $condition === 'new' ? 'selected' : '' ?>>New</option>
                        <option value="used" <?= $condition === 'used' ? 'selected' : '' ?>>Used</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Color</label>
                    <select name="color">
                        <option value="">Any</option>
                        <?php foreach($colors as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= $color === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Fuel</label>
                    <select name="fuel">
                        <option value="">Any</option>
                        <?php foreach($fuelTypes as $f): ?>
                            <option value="<?= htmlspecialchars($f) ?>" <?= $fuel === $f ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Price Max</label>
                    <input type="number" name="max_price" value="<?= htmlspecialchars($max_price) ?>" placeholder="e.g. 30000">
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn-apply">Apply Filters</button>
                    <a href="search.php" class="clear-filters">Clear</a>
                </div>
            </form>
        </div>

        <!-- Results Display -->
        <main class="results-container">
            <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: baseline; padding-left: 10px;">
                <h2 style="font-size: 1.8rem; color: var(--text-light);">Inventory Results</h2>
                <span style="font-weight: 600; color: #888;"><?= count($results) ?> vehicles found</span>
            </div>
            
            <?php if(count($results) > 0): ?>
                <div class="inventory-grid">
                    <?php foreach($results as $car): ?>
                        <a href="view_vehicle.php?vin=<?= urlencode($car['VIN']) ?>" style="text-decoration: none; color: inherit; display: block;">
                            <div style="padding: 24px; position: static; background: white; border: 1px solid var(--border-color); border-radius: 8px; display: flex; flex-direction: column; gap: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div>
                                        <h3 style="font-size: 1.4rem; color: var(--text-light); font-weight: 700;"><?= htmlspecialchars($car['Make'] . ' ' . $car['Model']) ?></h3>
                                        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 5px;"><?= htmlspecialchars($car['Vehicle_Year']) ?> &bull; <?= $car['NumOwners'] == 0 ? 'New' : 'Used' ?></p>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px; background: #f4f4f4; padding: 6px 12px; border-radius: 20px;">
                                        <div style="width: 14px; height: 14px; border-radius: 50%; border: 1px solid #ccc; background-color: <?= strtolower(htmlspecialchars($car['Color'])) ?>;"></div>
                                        <span style="font-size: 0.85rem; font-weight: 600; color: #333; text-transform: capitalize;"><?= htmlspecialchars($car['Color']) ?></span>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 10px; padding-top: 15px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--accent);">$<?= number_format($car['Price']) ?></div>
                                    <div style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;"><?= number_format($car['Mileage']) ?> mi</div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="background: white; padding: 60px 40px; text-align: center; border-radius: 8px; border: 1px solid #eee;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="2" style="margin-bottom: 20px;"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <h3 style="color: var(--text-dark); margin-bottom: 10px;">No exact matches found</h3>
                    <p style="color: #888;">Try adjusting or clearing some filters to expand your search.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
