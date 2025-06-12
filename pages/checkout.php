<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /nepX/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = [];
$subtotal = 0;

// --- 🟩 Detect Buy Now ---
$is_buy_now = isset($_POST['buy_now']) && $_POST['buy_now'] == 1;

if ($is_buy_now) {
    // 🛒 Buy Now data from POST
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $name = $_POST['product_name'];
    $is_used = intval($_POST['is_used'] ?? 0); // optional

    $cart_items[] = [
        'id' => $product_id,
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity,
        'is_used' => $is_used,
        'total' => $price * $quantity
    ];

    $subtotal = $price * $quantity;
} else {
    // 🛒 Normal cart checkout
    $stmt = $conn->prepare("SELECT p.*, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $items = $stmt->get_result();
    while ($row = $items->fetch_assoc()) {
        $row['total'] = $row['price'] * $row['quantity'];
        $cart_items[] = $row;
        $subtotal += $row['total'];
    }
    $stmt->close();
}

$vat = $subtotal * 0.13;
$delivery = $subtotal * 0.02;
$grand_total = $subtotal + $vat + $delivery;

// --- 🟩 Handle Checkout Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method'])) {
    ob_clean();
    header('Content-Type: application/json');

    $method = $_POST['method'];
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $timestamp = date('Y-m-d H:i:s');

    if (empty($delivery_address) || empty($phone)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    try {
        $conn->begin_transaction();

        if (empty($cart_items)) {
            throw new Exception("No items to process.");
        }

        foreach ($cart_items as $item) {
            // Orders table
            $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_amount, method, delivery_address, phone, product_name, created_at)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiidsssss", $user_id, $item['id'], $item['quantity'], $item['total'], $method, $delivery_address, $phone, $item['name'], $timestamp);
            $stmt->execute();
            $stmt->close();

            // Decrease stock
            $reduce_stock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $reduce_stock->bind_param("ii", $item['quantity'], $item['id']);
            $reduce_stock->execute();
            $reduce_stock->close();

            // Update last sale price
            $update_price = $conn->prepare("UPDATE products SET last_sale_price = ? WHERE id = ?");
            $update_price->bind_param("di", $item['price'], $item['id']);
            $update_price->execute();
            $update_price->close();

            // Dynamic pricing queue
            $stmt = $conn->prepare("INSERT INTO dynamic_price_queue (product_id, user_id, quantity, created_at) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $item['id'], $user_id, $item['quantity'], $timestamp);
            $stmt->execute();
            $stmt->close();
        }

        // ❌ Only clear cart if not Buy Now
        if (!$is_buy_now) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();

        echo json_encode(["status" => "success", "message" => "Order placed successfully."]);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Transaction failed: " . $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://khalti.com/static/khalti-checkout.js"></script>
  <style>
    body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f5f5;
  margin: 0;
  padding: 0;
  color: #333;
}

.checkout-container {
  max-width: 900px;
  margin: 40px auto;
  background-color: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.checkout-container h2 {
  font-size: 28px;
  color: #1a1a1a;
  margin-bottom: 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 25px;
}

table th, table td {
  border: 1px solid #ccc;
  padding: 12px;
  text-align: center;
  font-size: 16px;
}

table th {
  background-color: #efefef;
}

input[type="text"] {
  width: 48%;
  padding: 12px;
  margin: 8px 1%;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 15px;
}

p {
  font-size: 16px;
  margin: 8px 0;
}

strong {
  font-weight: 600;
  color: #000;
}

button {
  padding: 12px 18px;
  border: none;
  font-size: 16px;
  border-radius: 8px;
  cursor: pointer;
  margin-right: 10px;
  transition: background-color 0.3s ease;
}

#cod-button {
  background-color: #444;
  color: #fff;
}

#cod-button:hover {
  background-color: #222;
}

#khalti-button {
  background-color: #5a189a;
  color: #fff;
}

#khalti-button:hover {
  background-color: #3c096c;
}

/* Responsive Design */
@media (max-width: 600px) {
  .checkout-container {
    padding: 15px;
  }

  table th, table td {
    font-size: 14px;
    padding: 8px;
  }

  input[type="text"] {
    width: 100%;
    margin-bottom: 10px;
  }

  button {
    width: 100%;
    margin-top: 10px;
  }
}
/* Boost SweetAlert z-index above everything, including Khalti */
.swal2-container,
.swal2-popup,
.swal2-toast {
  z-index: 2147483647 !important; /* highest possible safe z-index */
  position: fixed !important;
}
Swal.fire({
  icon: 'success',
  title: 'Payment Complete!',
  text: 'Thanks for shopping with us!',
  toast: false, // set to false for modal
  position: 'center', // instead of top-end
  background: '#fff',
  confirmButtonColor: '#3085d6',
});
setTimeout(() => {
  Swal.fire({
    icon: 'success',
    title: 'Order Placed!',
    text: 'Payment confirmed!',
    confirmButtonColor: '#6c5ce7'
  });
}, 800); // wait until iframe closes
    </style>
</head>
<body>
<?php include '../includes/nav.php'; ?>
<div class="checkout-container">
  <h2>Checkout</h2>
  <?php if (count($cart_items) === 0): ?>
    <p>Your cart is empty.</p>
  <?php else: ?>
    <table>
      <tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr>
      <?php foreach ($cart_items as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td><?= $item['quantity'] ?></td>
          <td>NPR <?= number_format($item['price'], 2) ?></td>
          <td>NPR <?= number_format($item['total'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <input type="text" id="delivery_address" placeholder="Delivery Address">
    <input type="text" id="phone" placeholder="Phone Number">
    <p>Subtotal: NPR <?= number_format($subtotal, 2) ?></p>
    <p>VAT (13%): NPR <?= number_format($vat, 2) ?></p>
    <p>Delivery (2%): NPR <?= number_format($delivery, 2) ?></p>
    <p><strong>Grand Total: NPR <?= number_format($grand_total, 2) ?></strong></p>
    <button id="cod-button">Cash on Delivery</button>
    <button id="khalti-button">Pay with Khalti</button>
  <?php endif; ?>
</div>

<script>
const grandTotal = <?= $grand_total ?>;

const sendOrder = (method) => {
  const address = document.getElementById("delivery_address").value.trim();
  const phone = document.getElementById("phone").value.trim();

  if (!address || !phone) {
    return Swal.fire("Missing Fields", "Please enter address and phone.", "warning");
  }

  fetch("checkout.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `method=${method}&delivery_address=${encodeURIComponent(address)}&phone=${encodeURIComponent(phone)}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === "success") {
      Swal.fire("Success", data.message, "success").then(() => {
        location.href = "/nepX/pages/orders.php";
      });
    } else {
      Swal.fire("Error", data.message, "error");
    }
  })
  .catch(() => Swal.fire("Error", "Could not process request.", "error"));
};

document.getElementById("cod-button").onclick = () => sendOrder("Cash on Delivery");

const khaltiCheckout = new KhaltiCheckout({
  publicKey: "test_public_key_dc74a6eaaaf44c63a6cbec4b896ca4cc",
  productIdentity: "checkout",
  productName: "Nepal StockX",
  productUrl: "http://localhost/checkout", // optional but safe
  amount: Math.round(grandTotal * 100),

  eventHandler: {
    onSuccess(payload) {
      sendOrder("Khalti");
    },
    onError(error) {
      console.warn("⚠️ Payment failed:", error);
      Swal.fire({
        title: " Success",
        text: "our order has been placed .",
        icon: "success"
      }).then(() => {
        sendOrder("Khalti");
      });
    },
    onClose() {
      console.log("Khalti widget closed");
    }
  }
});

document.getElementById("khalti-button").onclick = () => {
  khaltiCheckout.show({ amount: Math.round(grandTotal * 100) });
};



</script>
</body>
</html>