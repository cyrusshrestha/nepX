<?php
// orders.php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /nepX/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders | Nepal StockX</title>
  <link rel="stylesheet" href="/nepX/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  <style>
    body {
  background-image: url('/nepX/uploads/background.png'); /* your image path */
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  min-height: 100vh;
  margin: 0;
  font-family: 'Instrument Sans', sans-serif;
}
    table {
      width: 100%;
      margin-top: 30px;
      border-collapse: collapse;
    }
    th, td {
      text-align: left;
      padding: 10px;
    }
    th {
      background-color: #f2f2f2;
    }
    tr:nth-child(even) {
      background-color: #fafafa;
    }
    tr:hover {
      background-color: #f1f1f1;
    }
  </style>
</head>
<body>

<?php include '../includes/nav.php'; ?>

<section class="orders" style="padding: 40px;">
  <h1><i class="fa-solid fa-box"></i> My Orders</h1>

  <?php if (count($orders) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Product Name</th>
          <th>Quantity</th>
          <th>Total Amount</th>
          <th>Payment Method</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
          <tr>
            <td><?= htmlspecialchars($order['product_name']) ?></td>
            <td><?= $order['quantity'] ?></td>
            <td>NPR <?= number_format($order['total_amount'], 2) ?></td>
            <td><?= htmlspecialchars($order['method']) ?></td>
            <td><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>You have not placed any orders yet.</p>
  <?php endif; ?>
</section>
</body>
</html>
