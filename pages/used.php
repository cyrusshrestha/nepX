<?php
session_start();
include '../includes/db.php';

$selectedCategory = $_GET['category'] ?? '';
$categories = ['Men', 'Women', 'Kid'];

if ($selectedCategory && in_array($selectedCategory, $categories)) {
  $stmt = $conn->prepare("SELECT * FROM used_products WHERE status = 'approved' AND category = ? ORDER BY id DESC");
  $stmt->bind_param("s", $selectedCategory);
} else {
  $stmt = $conn->prepare("SELECT * FROM used_products WHERE status = 'approved' ORDER BY id DESC");
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Used Products | Nepal StockX</title>
  <link rel="stylesheet" href="/nepX/style.css" />
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" crossorigin="anonymous" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php include '../includes/nav.php'; ?>

<section class="products" id="UsedProducts" style="padding: 40px;">
  <h1>✅ Approved Used Products</h1>

  <form method="GET" style="margin-bottom: 20px; text-align: center;">
    <label for="category">Filter by Category:</label>
    <select name="category" id="category" onchange="this.form.submit()">
      <option value="">All</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat ?>" <?= $cat === $selectedCategory ? 'selected' : '' ?>><?= $cat ?></option>
      <?php endforeach; ?>
    </select>
  </form>

  <div class="box">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="card">
        <div class="image">
          <img src="/nepX/uploads/used/<?= htmlspecialchars($row['shoe_image']) ?>" alt="<?= htmlspecialchars($row['model']) ?>">
        </div>

        <div class="products_text">
          <h2><?= htmlspecialchars($row['model']) ?> (<?= htmlspecialchars($row['brand']) ?>)</h2>
          <p><?= htmlspecialchars(substr($row['description'], 0, 60)) ?>...</p>
          <h3>NPR <?= number_format($row['price'], 2) ?></h3>
          <p>Size: <?= htmlspecialchars($row['size']) ?></p>
          <p>Category: <?= htmlspecialchars($row['category']) ?></p>
          <div class="products_star">
            <?php
              $stars = isset($row['rating']) ? floor($row['rating']) : 0;
              for ($i = 0; $i < $stars; $i++) echo '<i class="fa-solid fa-star"></i>';
              for ($i = $stars; $i < 5; $i++) echo '<i class="fa-regular fa-star"></i>';
            ?>
          </div>
          <div style="display: flex; gap: 10px; margin-top: 10px; justify-content: center;">
            <a href="/nepX/pages/product-details.php?id=<?= $row['id'] ?>&used=1" class="btn">View</a>

            <form method="POST" action="/nepX/pages/checkout.php" style="display: inline;">
              <input type="hidden" name="buy_now" value="1">
              <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
              <input type="hidden" name="price" value="<?= $row['price'] ?>">
              <input type="hidden" name="product_name" value="<?= htmlspecialchars($row['model']) ?>">
              <input type="hidden" name="quantity" value="1">
              <input type="hidden" name="is_used" value="1">
              <button class="btn">Buy Now</button>
            </form>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</section>

</body>
</html>