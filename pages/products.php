<?php
session_start();
include '../includes/db.php';
include '../admin/dynamic_pricing.php'; // dynamic pricing logic applied here

if (!isset($_GET['id'])) {
  echo "Product ID not found!";
  exit();
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
  echo "Product not found.";
  exit();
}

// Increment views
$conn->query("UPDATE products SET views = views + 1 WHERE id = $id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($product['name']) ?> | Nepal StockX</title>
  <link rel="stylesheet" href="/nepX/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"/>
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
    .product-details-container {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      padding: 40px;
      max-width: 900px;
      margin: auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .product-image img {
      width: 100%;
      max-width: 400px;
      border-radius: 8px;
    }
    .product-info h1 {
      margin-bottom: 10px;
    }
    .rating i {
      color: gold;
      margin-right: 3px;
    }
    .btn {
      background-color: #111;
      color: #fff;
      padding: 10px 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
    }
    .btn:hover {
      background-color: #333;
    }
    textarea {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      resize: vertical;
      margin-bottom: 10px;
    }
    .comment-section {
      width: 100%;
      margin-top: 40px;
    }
  </style>
</head>
<body>

<?php include '../includes/nav.php'; ?>

<div class="product-details-container">
  <div class="product-image">
  <img src="/nepX/uploads/<?= htmlspecialchars($product['image']) ?>" 
     onerror="this.onerror=null;this.src='/nepX/images/no-image.png';" 
     alt="<?= htmlspecialchars($product['name']) ?>">
  </div>

  <div class="product-info">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <p><strong>Brand:</strong> <?= htmlspecialchars($product['brand']) ?></p>
    <p><strong>Size:</strong> <?= htmlspecialchars($product['size']) ?> | 
       <strong>Stock:</strong> <?= (int)$product['quantity'] ?></p>
    <h3>
      NPR <?= number_format($product['price'], 2) ?>
      <?php if (!empty($product['last_sale_price']) && $product['last_sale_price'] > 0 && $product['last_sale_price'] != $product['price']): ?>
        <span style="color: gray; text-decoration: line-through; font-size: 14px;">
          NPR <?= number_format($product['last_sale_price'], 2) ?>
        </span>
        <span style="color: <?= $product['price'] > $product['last_sale_price'] ? 'red' : 'green' ?>; font-size: 13px;">
          <?= $product['price'] > $product['last_sale_price'] ? '↑ Price Increased' : '↓ Price Dropped' ?>
        </span>
      <?php endif; ?>
    </h3>
    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

    <div class="rating">
      <?php
        $stars = floor($product['rating']);
        for ($i = 0; $i < $stars; $i++) echo '<i class="fa-solid fa-star"></i>';
        for ($i = $stars; $i < 5; $i++) echo '<i class="fa-regular fa-star"></i>';
      ?>
    </div>

    <div style="margin-top: 15px;">
      <button class="btn add-to-cart" data-id="<?= $product['id'] ?>">🛒 Add to Cart</button>
      <button class="btn wishlist-btn" data-id="<?= $product['id'] ?>">❤️ Wishlist</button>
    </div>
  </div>
</div>

<div class="product-details-container">
  <div class="comment-section">
    <h3>🗣️ Comments</h3>
    <?php if (isset($_SESSION['user_id'])): ?>
      <form method="POST" action="post-comment.php">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <textarea name="comment" placeholder="Leave your comment..." required></textarea>
        <button type="submit" class="btn">Post Comment</button>
      </form>
    <?php else: ?>
      <p><a href="/nepX/auth/login.php">Login</a> to comment.</p>
    <?php endif; ?>

    <div class="comments-list">
      <?php
        $comments = $conn->query("SELECT * FROM product_comments WHERE product_id = $id ORDER BY created_at DESC");
        while ($c = $comments->fetch_assoc()):
      ?>
        <div style="margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
          <strong><?= htmlspecialchars($c['username']) ?>:</strong>
          <p><?= htmlspecialchars($c['comment']) ?></p>
          <small style="color: #888;"><?= date("M d, Y", strtotime($c['created_at'])) ?></small>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</div>

<script src="/nepX/js/wishlist.js"></script>
<script src="/nepX/js/cart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
