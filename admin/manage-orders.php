<?php
session_start();
include '../includes/db.php';
include '../admin/admin-nav.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
  $orderId = (int)$_POST['order_id'];
  $action = $_POST['action'];

  if (in_array($action, ['approved', 'rejected'])) {
    $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $updateStmt->bind_param("si", $action, $orderId);
    $updateStmt->execute();

    $stmt = $conn->prepare("SELECT user_id, id AS order_id, product_name FROM orders WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
      $user_id = $result['user_id'];
      $order_id = $result['order_id'];
      $product_name = $result['product_name'];

      $message = $action === 'approved'
        ? "🚚 Boom! Your order #$order_id for <b>$product_name</b> has been approved and is on its way!"
        : "❌ Sorry! Your order #$order_id for <b>$product_name</b> was rejected. Please contact support.";

      $notiStmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
      $notiStmt->bind_param("is", $user_id, $message);
      $notiStmt->execute();
    }
  }
}

$result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Orders</title>
  <link rel="stylesheet" href="/nepX/style.css">
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }
    th, td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: center;
    }
    th {
      background-color: #f4f4f4;
    }
    .btn {
      padding: 6px 10px;
      margin: 2px;
      border: none;
      border-radius: 5px;
      color: #fff;
      font-weight: bold;
      cursor: pointer;
    }
    .btn-approve {
      background-color: #28a745;
    }
    .btn-reject {
      background-color: #dc3545;
    }
  </style>
</head>
<body>
  <h2 style="text-align:center; margin: 30px 0;">📦 Manage Orders</h2>
  <table>
    <thead>
      <tr>
        <th>Order ID</th>
        <th>User ID</th>
        <th>Amount</th>
        <th>Method</th>
        <th>Status</th>
        <th>Product</th>
        <th>Address</th>
        <th>Phone</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= $row['user_id'] ?></td>
          <td>NPR <?= number_format($row['total_amount'], 2) ?></td>
          <td><?= $row['method'] ?></td>
          <td><?= ucfirst($row['status']) ?></td>
          <td><?= htmlspecialchars($row['product_name']) ?></td>
          <td><?= htmlspecialchars($row['delivery_address']) ?></td>
          <td><?= htmlspecialchars($row['phone']) ?></td>
          <td>
            <?php if ($row['status'] === 'pending'): ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                <button type="submit" name="action" value="approved" class="btn btn-approve">Approve</button>
                <button type="submit" name="action" value="rejected" class="btn btn-reject">Reject</button>
              </form>
            <?php else: ?>
              <?= ucfirst($row['status']) === 'Approved' ? '✅ Approved' : '❌ Rejected' ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>