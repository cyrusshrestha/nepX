<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /nepX/auth/login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Handle remove action securely
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
  $cartId = intval($_POST['cart_id']);
  $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ii", $cartId, $user_id);
  $stmt->execute();
  $stmt->close();
  header("Location: cart.php");
  exit();
}

// Fetch cart items
$query = $conn->prepare("SELECT c.id AS cart_id, p.name, p.price, p.image, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$cartItems = $result->fetch_all(MYSQLI_ASSOC);
$query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Cart | Nepal StockX</title>
  <link rel="stylesheet" href="/nepX/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
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
    .btn {
      background-color: black;
      color: white;
      padding: 10px 20px;
      border-radius: 6px;
      text-decoration: none;
    }
    .btn:hover {
      background-color: #333;
    }
  </style>
</head>
<body>

<?php include '../includes/nav.php'; ?>

<section class="cart" style="padding: 40px;">
  <h1><i class="fa-solid fa-cart-shopping"></i> My Cart</h1>

  <?php if (count($cartItems) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Qty</th>
          <th>Total</th>
          <th>Remove</th>
        </tr>
      </thead>
      <tbody>
        <?php $grandTotal = 0; ?>
        <?php foreach ($cartItems as $row): ?>
          <?php $total = $row['price'] * $row['quantity']; $grandTotal += $total; ?>
          <tr>
            <td style="display: flex; align-items: center; gap: 15px;">
              <img src="/nepX/uploads/<?= htmlspecialchars($row['image']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
              <?= htmlspecialchars($row['name']) ?>
            </td>
            <td>NPR <?= number_format($row['price'], 2) ?></td>
            <td><?= $row['quantity'] ?></td>
            <td>NPR <?= number_format($total, 2) ?></td>
            <td>
              <form method="POST" action="">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="cart_id" value="<?= $row['cart_id'] ?>">
                <button type="submit" style="background-color: red; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">❌</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h2 style="margin-top: 20px;">Grand Total: <strong>NPR <?= number_format($grandTotal, 2) ?></strong></h2>
    <a href="/nepX/pages/checkout.php" class="btn" style="margin-top: 20px; display: inline-block;">Proceed to Checkout</a>
  <?php else: ?>
    <p>Your cart is empty.</p>
  <?php endif; ?>
</section>


</body>
</html>
