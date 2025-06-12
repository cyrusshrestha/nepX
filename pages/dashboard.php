<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: /nepX/auth/login.php");
  exit();
}
include '../includes/db.php';
include '../admin/dynamic_pricing.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | Nepal StockX</title>
  <link rel="stylesheet" href="/nepX/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" crossorigin="anonymous" />
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
</style>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php include '../includes/nav.php'; ?>

<!-- 🔥 Trending Section -->
<section class="products" id="Trending">
  <h1>🔥 Trending Shoes</h1>
  <div class="box">
    <?php
    $trending = $conn->query("SELECT * FROM products WHERE quantity > 0 ORDER BY views DESC LIMIT 6");
    while ($row = $trending->fetch_assoc()):
      $image = file_exists($_SERVER['DOCUMENT_ROOT'] . "/nepX/uploads/" . $row['image']) ? $row['image'] : "placeholder.jpg";
      $price = number_format($row['price'], 2);
      $last_price = $row['last_sale_price'];
    ?>
      <div class="card">
        <div class="small_card">
          <button class="wishlist-btn" data-id="<?= $row['id'] ?>" style="background: none; border: none;">
            <i class="fa-solid fa-heart"></i>
          </button>
          <i class="fa-solid fa-share"></i>
        </div>
        <div class="image">
          <img src="/nepX/uploads/<?= $image ?>" alt="<?= htmlspecialchars($row['name']) ?>">
        </div>
        <div class="products_text">
        <p style="font-size: 13px; color: #888; margin-top: -5px;">
    Brand: <strong><?= htmlspecialchars($row['brand']) ?></strong>
  </p>
          <h2><?= htmlspecialchars($row['name']) ?></h2>
          <h3>NPR <?= $price ?></h3>

          <?php if (!is_null($last_price) && $last_price > 0 && $last_price != $row['price']): ?>
            <p style="color: <?= $row['price'] > $last_price ? 'red' : 'green' ?>;">
              <?= $row['price'] > $last_price ? '🔥 Trending — price increased recently.' : '📉 Price dropped — grab it now!' ?>
            </p>
          <?php endif; ?>

          <?php if ($row['quantity'] < 5): ?>
            <p style="color: #e91e63; font-weight: bold;">⚠️ Only <?= $row['quantity'] ?> left in stock!</p>
          <?php endif; ?>

          <div class="products_star">
            <?php
              $stars = isset($row['rating']) ? floor($row['rating']) : 0;
              for ($i = 0; $i < $stars; $i++) echo '<i class="fa-solid fa-star"></i>';
              for ($i = $stars; $i < 5; $i++) echo '<i class="fa-regular fa-star"></i>';
            ?>
          </div>

          <div style="display: flex; gap: 10px; margin-top: 10px;">
            <a href="/nepX/pages/product-details.php?id=<?= $row['id'] ?>" class="btn">View</a>
            <button class="btn add-to-cart" data-id="<?= $row['id'] ?>">Add to Cart</button>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</section>

<!-- 🆕 New Arrivals Section -->
<section class="products" id="NewArrivals" style="margin-top: 40px;">
  <h1>🆕 New Arrivals</h1>
  <div class="box">
    <?php
    $new = $conn->query("SELECT * FROM products WHERE quantity > 0 ORDER BY id DESC LIMIT 6");
    while ($row = $new->fetch_assoc()):
      $image = file_exists($_SERVER['DOCUMENT_ROOT'] . "/nepX/uploads/" . $row['image']) ? $row['image'] : "placeholder.jpg";
      $price = number_format($row['price'], 2);
      $last_price = $row['last_sale_price'];
    ?>
      <div class="card">
        <div class="small_card">
          <button class="wishlist-btn" data-id="<?= $row['id'] ?>" style="background: none; border: none;">
            <i class="fa-solid fa-heart"></i>
          </button>
          <i class="fa-solid fa-share"></i>
        </div>
        <div class="image">
          <img src="/nepX/uploads/<?= $image ?>" alt="<?= htmlspecialchars($row['name']) ?>">
        </div>
        <div class="products_text">
        <p style="font-size: 13px; color: #888; margin-top: -5px;">
    Brand: <strong><?= htmlspecialchars($row['brand']) ?></strong>
  </p>
          <h2><?= htmlspecialchars($row['name']) ?></h2>
          <h3>NPR <?= $price ?></h3>

          <?php if (!is_null($last_price) && $last_price > 0 && $last_price != $row['price']): ?>
            <p style="color: <?= $row['price'] > $last_price ? 'red' : 'green' ?>;">
              <?= $row['price'] > $last_price ? '🔥 Trending — price increased recently.' : '📉 Price dropped — grab it now!' ?>
            </p>
          <?php endif; ?>

          <?php if ($row['quantity'] < 5): ?>
            <p style="color: #e91e63; font-weight: bold;">⚠️ Only <?= $row['quantity'] ?> left in stock!</p>
          <?php endif; ?>

          <div class="products_star">
            <?php
              $stars = isset($row['rating']) ? floor($row['rating']) : 0;
              for ($i = 0; $i < $stars; $i++) echo '<i class="fa-solid fa-star"></i>';
              for ($i = $stars; $i < 5; $i++) echo '<i class="fa-regular fa-star"></i>';
            ?>
          </div>

          <div style="display: flex; gap: 10px; margin-top: 10px;">
            <a href="/nepX/pages/product-details.php?id=<?= $row['id'] ?>" class="btn">View</a>
            <button class="btn add-to-cart" data-id="<?= $row['id'] ?>">Add to Cart</button>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</section>

<script src="/nepX/js/wishlist.js"></script>
<script src="/nepX/js/cart.js"></script>
</body>
</html>