<?php
// nepX/admin/trigger_dynamic_pricing.php
session_start();
include '../includes/db.php';
include '../admin/admin-nav.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$now = date('Y-m-d H:i:s');
$one_hour_ago = date('Y-m-d H:i:s', strtotime('-60 minutes'));
$output = [];

$products = $conn->query("SELECT id, price FROM products");

while ($product = $products->fetch_assoc()) {
    $pid = $product['id'];
    $old_price = floatval($product['price']);

    $recent = $conn->prepare("SELECT SUM(quantity) as qty FROM dynamic_price_queue WHERE product_id = ? AND created_at >= ?");
    $recent->bind_param("is", $pid, $one_hour_ago);
    $recent->execute();
    $result = $recent->get_result()->fetch_assoc();
    $recent_qty = (int) ($result['qty'] ?? 0);
    $recent->close();

    // Pricing logic
    if ($recent_qty >= 10) {
        $new_price = round($old_price * 1.05, 2);
        $label = "📈 Massive demand (+5%) (Qty: $recent_qty)";
    } elseif ($recent_qty >= 5) {
        $new_price = round($old_price * 1.03, 2);
        $label = "📈 High demand (+3%) (Qty: $recent_qty)";
    } elseif ($recent_qty >= 3) {
        $new_price = round($old_price * 1.02, 2);
        $label = "📈 Moderate demand (+2%) (Qty: $recent_qty)";
    } elseif ($recent_qty >= 1) {
        $new_price = round($old_price * 1.01, 2);
        $label = "📈 Low demand (+1%) (Qty: $recent_qty)";
    } else {
        $new_price = round($old_price * 0.98, 2);
        $label = "📉 No orders (-2%) (Qty: 0)";
    }

    if ($new_price != $old_price) {
        $stmt = $conn->prepare("UPDATE products SET price = ? WHERE id = ?");
        $stmt->bind_param("di", $new_price, $pid);
        $stmt->execute();
        $stmt->close();

        $log = $conn->prepare("INSERT INTO price_history (product_id, old_price, new_price, created_at) VALUES (?, ?, ?, NOW())");
        $log->bind_param("idd", $pid, $old_price, $new_price);
        $log->execute();
        $log->close();

        $output[] = "<span style='color:green'>✅ Product $pid: NPR $old_price → NPR $new_price</span> ($label)";
    } else {
        $output[] = "<span style='color:gray'>📦 Product $pid unchanged (Qty in 60 min: $recent_qty)</span>";
    }

    // Only delete entries used for current pricing window
    $delete = $conn->prepare("DELETE FROM dynamic_price_queue WHERE product_id = ? AND created_at >= ?");
    $delete->bind_param("is", $pid, $one_hour_ago);
    $delete->execute();
    $delete->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Run Dynamic Pricing</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; text-align: center; padding: 30px; }
        .box { max-width: 700px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        button { padding: 12px 24px; background: #28a745; color: white; border: none; cursor: pointer; font-size: 16px; border-radius: 5px; }
        button:hover { background: #218838; }
        .log { background: #f0f0f0; padding: 20px; text-align: left; font-family: monospace; white-space: pre-wrap; border-radius: 6px; margin-top: 20px; border: 1px solid #ccc; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="box">
        <h2>🧠 Manual Dynamic Pricing Trigger</h2>
        <form method="post">
            <button type="submit">Run Dynamic Pricing</button>
        </form>

        <?php if (!empty($output)): ?>
            <div class="log">
                <?php foreach ($output as $line) echo "$line<br>"; ?>
                <br><strong>✅ Dynamic pricing check complete.</strong>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>