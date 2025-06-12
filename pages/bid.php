<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /nepX/auth/login.php");
  exit();
}

// Handle bid submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $product_id = $_POST['product_id'];
  $bid_amount = $_POST['bid_amount'];

  $checkStmt = $conn->prepare("SELECT id FROM product_bids WHERE user_id = ? AND product_id = ?");
  $checkStmt->bind_param("ii", $user_id, $product_id);
  $checkStmt->execute();
  $checkStmt->store_result();

  if ($checkStmt->num_rows > 0) {
    $_SESSION['bid_success'] = "❌ You have already placed a bid on this product.";
    header("Location: bid.php");
    exit();
  }

  $stmt = $conn->prepare("INSERT INTO product_bids (product_id, user_id, bid_amount) VALUES (?, ?, ?)");
  $stmt->bind_param("iid", $product_id, $user_id, $bid_amount);
  $stmt->execute();

  $_SESSION['bid_success'] = "✅ Bid placed successfully!";
  header("Location: bid.php");
  exit();
}

$products = $conn->query("SELECT * FROM bid_products ORDER BY end_time ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bidding Products | Nepal StockX</title>
  <link rel="stylesheet" href="/nepX/style.css">
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
    .bid-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 24px;
      padding: 30px 40px;
    }
    .bid-card {
      background: #ffffff;
      border-radius: 16px;
      padding: 18px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .bid-card:hover {
      transform: scale(1.02);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }
    .bid-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 10px;
    }
    .bid-card h3 {
      font-size: 20px;
      margin: 12px 0 8px;
      color: #222;
    }
    .timer {
      color: #c72092;
      font-weight: bold;
      margin-bottom: 10px;
    }
    .bid-form input {
      padding: 8px;
      border-radius: 6px;
      border: 1px solid #ccc;
      width: 100px;
    }
    .bid-form button {
      padding: 8px 14px;
      border: none;
      background-color: #6c14d0;
      color: white;
      border-radius: 6px;
      cursor: pointer;
      margin-left: 10px;
    }
    .winner {
      color: #28a745;
      font-weight: bold;
      margin-top: 10px;
    }
    .error {
      color: #e91e63;
      font-weight: bold;
      margin-top: 10px;
    }
    .pay-btn {
      display: inline-block;
      background: #6c14d0;
      color: white;
      padding: 8px 16px;
      border-radius: 6px;
      text-decoration: none;
      margin-top: 10px;
    }
  </style>
  <script>
    function startCountdown(endTimeStr, elementId) {
      const endTime = new Date(endTimeStr).getTime();
      const timer = setInterval(() => {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
          document.getElementById(elementId).innerText = "⏳ Bidding closed";
          clearInterval(timer);
          return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById(elementId).innerText = `${hours}h ${minutes}m ${seconds}s remaining`;
      }, 1000);
    }
  </script>
</head>
<body>

<?php include '../includes/nav.php'; ?>

<section style="padding: 30px;">
  <h1 style="text-align:center; color:#6c14d0;">🕒 Active Bidding Products</h1>

  <?php if (isset($_SESSION['bid_success'])): ?>
    <p style="text-align:center; color: <?= strpos($_SESSION['bid_success'], '❌') !== false ? 'red' : 'green'; ?>; font-weight: bold;">
      <?= $_SESSION['bid_success']; unset($_SESSION['bid_success']); ?>
    </p>
  <?php endif; ?>

  <div class="bid-grid">
    <?php while ($product = $products->fetch_assoc()): ?>
      <div class="bid-card">
        <img src="/nepX/uploads/bids/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.onerror=null;this.src='/nepX/uploads/placeholder.jpg';">
        <h3><?= htmlspecialchars($product['name']) ?></h3>
        <p><?= htmlspecialchars($product['description']) ?></p>
        <p>Starting at: <strong>NPR <?= number_format($product['start_price'], 2) ?></strong></p>

        <p class="timer" id="timer<?= $product['id'] ?>">Loading...</p>
        <script>startCountdown("<?= $product['end_time'] ?>", "timer<?= $product['id'] ?>");</script>

        <?php
          $bidStmt = $conn->prepare("SELECT MAX(bid_amount) as highest FROM product_bids WHERE product_id = ?");
          $bidStmt->bind_param("i", $product['id']);
          $bidStmt->execute();
          $highestBid = $bidStmt->get_result()->fetch_assoc()['highest'] ?? $product['start_price'];
        ?>
        <p>Current Highest Bid: NPR <?= number_format($highestBid, 2) ?></p>

        <?php if (strtotime($product['end_time']) > time()): ?>
          <form method="POST" class="bid-form">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="number" step="0.01" name="bid_amount" placeholder="Your Bid" required>
            <button type="submit">Place Bid</button>
          </form>
        <?php else: ?>
          <p class="error">⛔ Bidding has ended.</p>
          <?php
            $winnerStmt = $conn->prepare("SELECT users.username, pb.user_id, pb.bid_amount as max_bid 
                                          FROM product_bids pb 
                                          JOIN users ON pb.user_id = users.id 
                                          WHERE pb.product_id = ? 
                                          ORDER BY pb.bid_amount DESC 
                                          LIMIT 1");
            $winnerStmt->bind_param("i", $product['id']);
            $winnerStmt->execute();
            $winner = $winnerStmt->get_result()->fetch_assoc();
          ?>
          <?php if ($winner && $winner['max_bid']): ?>
            <p class="winner">🏆 Winner: <?= htmlspecialchars($winner['username']) ?> - NPR <?= number_format($winner['max_bid'], 2) ?></p>
            <?php if ($_SESSION['user_id'] == $winner['user_id']): ?>
              <a href="/nepX/pages/pay-bid.php?product_id=<?= $product['id'] ?>" class="pay-btn">💳 Proceed to Payment</a>
            <?php endif; ?>
          <?php else: ?>
            <p>No bids placed.</p>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  </div>
</section>

</body>
</html>