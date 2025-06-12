<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include '../includes/db.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$is_used = isset($_GET['used']) && $_GET['used'] == 1;

if (!$id) {
  echo "Product ID is missing.";
  exit();
}

if ($is_used) {
  $stmt = $conn->prepare("SELECT * FROM used_products WHERE id = ? AND status = 'approved'");
} else {
  $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
}
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
  echo "Product not found.";
  exit();
}

$prices = $dates = [];
if (!$is_used) {
  $last_sale = $product['last_sale_price'];
  $history_stmt = $conn->prepare("SELECT new_price, created_at FROM price_history WHERE product_id = ? ORDER BY id ASC");
  $history_stmt->bind_param("i", $id);
  $history_stmt->execute();
  $history_result = $history_stmt->get_result();
  while ($row = $history_result->fetch_assoc()) {
    $prices[] = $row['new_price'];
    $dates[] = date('M d', strtotime($row['created_at']));
  }
  $history_stmt->close();
}

$comment_stmt = $conn->prepare("SELECT u.username, c.comment, c.created_at FROM product_comments c JOIN users u ON c.user_id = u.id WHERE c.product_id = ?");
$comment_stmt->bind_param("i", $id);
$comment_stmt->execute();
$comments = $comment_stmt->get_result();

$avg_rating_query = $conn->prepare("SELECT AVG(rating) as avg_rating FROM product_ratings WHERE product_id = ?");
$avg_rating_query->bind_param("i", $id);
$avg_rating_query->execute();
$avg_rating = round($avg_rating_query->get_result()->fetch_assoc()['avg_rating'] ?? 0, 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($is_used ? $product['model'] : $product['name']) ?> | Nepal StockX</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    body {
  font-family: 'Instrument Sans', sans-serif;
  background: #f9f9f9;
  margin: 0;
  padding: 0;
}

.product-details-container {
  max-width: 1100px;
  margin: 40px auto;
  display: flex;
  gap: 40px;
  padding: 20px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}

.product-image img {
  max-width: 400px;
  width: 100%;
  border-radius: 12px;
  object-fit: contain;
  background: #fff;
}

.product-info {
  flex: 1;
}

.product-info h1 {
  font-size: 32px;
  margin-bottom: 10px;
  color: #6c14d0;
}

.product-info p {
  margin: 8px 0;
  color: #444;
  line-height: 1.6;
}

.btn {
  background: linear-gradient(to right, #c72092, #6c14d0);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  font-size: 14px;
  transition: all 0.3s ease;
}

.btn:hover {
  opacity: 0.9;
}

.rating-comment-container {
  max-width: 800px;
  margin: 40px auto;
  padding: 20px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
}

.rating-comment-container h2 {
  color: #6c14d0;
  margin-bottom: 15px;
}

.rating-comment-container form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.rating-comment-container textarea,
.rating-comment-container select {
  padding: 10px;
  font-size: 15px;
  border-radius: 8px;
  border: 1px solid #ccc;
  resize: vertical;
}

.rating-comment-container .btn {
  width: fit-content;
  padding: 10px 20px;
  background: linear-gradient(to right, #c72092, #6c14d0);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.rating-comment-container .btn:hover {
  opacity: 0.9;
}

.review {
  margin-top: 30px;
}

.review h3 {
  margin-bottom: 10px;
  color: #333;
}

.review-item {
  border-bottom: 1px solid #eee;
  padding: 12px 0;
}

.review-item strong {
  color: #6c14d0;
  font-size: 16px;
}

.review-item small {
  color: #888;
  font-size: 12px;
}

#priceChart {
  max-width: 1000px; /* ✅ Increased width */
  width: 90%;
  margin: 40px auto;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  padding: 30px;
}
</style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include '../includes/nav.php'; ?>

<?php if (isset($_SESSION['bid_success'])): ?>
  <div style="background:#dff0d8;color:#3c763d;padding:12px;text-align:center;">
    <?= $_SESSION['bid_success']; unset($_SESSION['bid_success']); ?>
  </div>
<?php endif; ?>

<div class="product-details-container">
  <div class="product-image">
    <img src="<?= $is_used ? '/nepX/uploads/used/' . htmlspecialchars($product['shoe_image']) : '/nepX/uploads/' . htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($is_used ? $product['model'] : $product['name']) ?>">
  </div>
  <div class="product-info">
    <h1><?= htmlspecialchars($is_used ? $product['model'] : $product['name']) ?></h1>
    <p><strong>Brand:</strong> <?= htmlspecialchars($product['brand'] ?? 'N/A') ?></p>
    <p><strong>Price:</strong> NPR <?= number_format($product['price'], 2) ?></p>
    <?php if (!$is_used && isset($last_sale)): ?>
      <p><strong>Last Sale:</strong> NPR <?= number_format($last_sale, 2) ?></p>
      <p style="color:<?= $product['price'] > $last_sale ? 'red' : 'green' ?>;">
        <?= $product['price'] > $last_sale ? '🔥 Trending — price increased recently.' : '📉 Price dropped — grab it now!' ?>
      </p>
    <?php endif; ?>
    <p><strong>Size:</strong> <?= htmlspecialchars($product['size']) ?></p>
    <p><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
    <p><strong>Description:</strong><br><?= htmlspecialchars($product['description']) ?></p>
    <p><strong>Average Rating:</strong> <?= $avg_rating ?>/5 ⭐</p>

    <?php if (!$is_used): ?>
      <button class="btn add-cart-btn" data-id="<?= $product['id'] ?>">🛒 Add to Cart</button>
      <button class="btn wishlist-btn" data-id="<?= $product['id'] ?>">❤️ Wishlist</button>
    <?php endif; ?>

    <form method="post" action="/nepX/pages/checkout.php" style="display:inline;">
      <input type="hidden" name="buy_now" value="1">
      <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
      <input type="hidden" name="price" value="<?= $product['price'] ?>">
      <input type="hidden" name="product_name" value="<?= htmlspecialchars($is_used ? $product['model'] : $product['name']) ?>">
      <input type="hidden" name="quantity" value="1">
      <input type="hidden" name="is_used" value="<?= $is_used ? 1 : 0 ?>">
      <button class="btn">Buy Now</button>
    </form>
  </div>
</div>

<div class="rating-comment-container">
  <h2>⭐ Rate & Comment</h2>
  <?php if (isset($_SESSION['user_id'])): ?>
    <form method="POST" action="submit_comment.php">
      <input type="hidden" name="product_id" value="<?= $id ?>">
      <select name="rating" required>
        <option value="">Choose Rating</option>
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
      </select>
      <textarea name="comment" placeholder="Write a comment..." required></textarea>
      <button type="submit" class="btn">Submit</button>
    </form>
  <?php else: ?>
    <p>Please <a href="/nepX/auth/login.php">login</a> to comment.</p>
  <?php endif; ?>

  <div class="review">
    <h3>💬 Reviews:</h3>
    <?php while ($row = $comments->fetch_assoc()): ?>
      <div class="review-item">
        <strong><?= htmlspecialchars($row['username']) ?></strong><br>
        <?= htmlspecialchars($row['comment']) ?><br>
        <small><?= $row['created_at'] ?></small>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<?php if (!$is_used): ?>
<canvas id="priceChart" width="400" height="150"></canvas>
<script>
const ctx = document.getElementById('priceChart');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?= json_encode($dates) ?>,
    datasets: [{
      label: 'Price History',
      data: <?= json_encode($prices) ?>,
      borderColor: 'purple',
      backgroundColor: 'rgba(128,0,128,0.1)',
      borderWidth: 2,
      fill: true
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: true } },
    scales: { y: { beginAtZero: false } }
  }
});
</script>
<?php endif; ?>

<script>
document.querySelector('.wishlist-btn')?.addEventListener('click', () => {
  const id = event.target.dataset.id;
  fetch('/nepX/server/controller.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add_to_wishlist&product_id=${id}`
  }).then(res => res.json()).then(data => alert(data.message));
});

document.querySelector('.add-cart-btn')?.addEventListener('click', () => {
  const id = event.target.dataset.id;
  fetch('/nepX/server/controller.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add_to_cart&product_id=${id}&quantity=1`
  }).then(res => res.json()).then(data => alert(data.message));
});
</script>
</body>
</html>
