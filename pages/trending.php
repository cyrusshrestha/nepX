<?php
session_start();
include '../includes/db.php';
include '../admin/dynamic_pricing.php';

$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Sorting
$sort = $_GET['sort'] ?? 'views';
$order = "ORDER BY views DESC";
if ($sort === 'low') $order = "ORDER BY price ASC";
elseif ($sort === 'high') $order = "ORDER BY price DESC";
elseif ($sort === 'new') $order = "ORDER BY id DESC";

// Price filtering
$min_price = isset($_GET['min']) ? floatval($_GET['min']) : 0;
$max_price = isset($_GET['max']) ? floatval($_GET['max']) : 99999;
$price_filter = "price BETWEEN $min_price AND $max_price AND quantity > 0";

// Total count for pagination
$totalRows = $conn->query("SELECT COUNT(*) as total FROM products WHERE $price_filter")->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Final query
$query = "SELECT * FROM products WHERE $price_filter $order LIMIT $limit OFFSET $offset";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>🔥 Trending Shoes | Nepal StockX</title>
  <link rel="stylesheet" href="/nepX/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .auth-box {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}
    body {
      background-image: url('/nepX/uploads/background.png'); /* your image path */
      background-size: cover;
       background-repeat: no-repeat;
      background-position: center;
      min-height: 100vh;
      margin: 0;
      font-family: 'Instrument Sans', sans-serif;
}
    .sort-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 15px;
      padding: 20px 40px;
      background: #f8f8f8;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      margin-bottom: 20px;
    }
    .sort-bar select,
    .sort-bar input[type="number"] {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      font-family: 'Instrument Sans', sans-serif;
      outline: none;
    }
    .sort-bar button {
      padding: 8px 16px;
      background: #6c14d0;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
    }
    .pagination {
      text-align: center;
      margin-top: 20px;
    }
    .pagination a {
      padding: 8px 12px;
      margin: 0 4px;
      background: white;
      border: 1px solid #ccc;
      text-decoration: none;
      border-radius: 5px;
      color: #333;
    }
    .pagination a.active {
      background-color: #6c14d0;
      color: white;
    }
    .card {
      padding: 20px;
      border: none;
      border-radius: 20px;
      background: #ffffff;
      text-align: center;
      box-shadow: 0 8px 24px rgba(108, 20, 208, 0.12);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      min-height: 480px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
    }
    .card .image img {
      height: 120px;
      object-fit: contain;
    }
    .products_text h2 {
      font-size: 20px;
      color: #222;
      font-weight: 600;
      margin: 4px 0;
    }
    .products_text p {
      font-size: 13px;
      color: #666;
      margin-bottom: 5px;
    }
    .products_text h3 {
      font-size: 16px;
      color: #c72092;
      font-weight: 600;
      margin: 8px 0 4px;
    }
    .products_star {
      color: orange;
    }
    .card .small_card {
  position: absolute;
  top: 16px;
  right: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  z-index: 1;
}

.card .small_card button,
.card .small_card i {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  font-size: 16px;
  border: 2px solid #999;
  background: #fff;
  color: #333;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.3s ease;
}

.card .small_card button:hover {
  background: #f3f3f3;
  color: #c72092;
  border-color: #c72092;
}
.sort-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1px;
  padding: 20px 40px;
  background: #f8f8f8;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  margin-bottom: 10px; /* reduce from 20px to 10px */
}

.box {
  margin-top: 1; /* ensure no additional top space */
}
section {
  background: transparent;
}

.bid-grid {
  background: transparent;
}
  </style>
</head>
<body>

<?php include '../includes/nav.php'; ?>

<section class="products" id="Trending">
  <h1>🔥 Trending Shoes</h1>
  <div class="sort-bar">
    <form method="GET">
      <label>Sort:</label>
      <select name="sort" onchange="this.form.submit()">
        <option value="views" <?= $sort === 'views' ? 'selected' : '' ?>>Most Viewed</option>
        <option value="low" <?= $sort === 'low' ? 'selected' : '' ?>>Price: Low → High</option>
        <option value="high" <?= $sort === 'high' ? 'selected' : '' ?>>Price: High → Low</option>
        <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Newest</option>
      </select>
      <label style="margin-left: 10px;">Min:</label>
      <input type="number" name="min" min="0" value="<?= $min_price ?>" />
      <label style="margin-left: 10px;">Max:</label>
      <input type="number" name="max" min="0" value="<?= $max_price ?>" />
      <button type="submit">Apply</button>
    </form>
  </div>

  <div class="box">
    <?php while ($row = $result->fetch_assoc()):
      $imagePath = "/nepX/uploads/" . $row['image'];
      $serverPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
      if (!file_exists($serverPath)) $imagePath = "/nepX/uploads/placeholder.jpg";
    ?>
    <div class="card">
      <div class="small_card">
        <button class="wishlist-btn" data-id="<?= $row['id'] ?>" style="background: none; border: none;">
          <i class="fa-solid fa-heart"></i>
        </button>
        <i class="fa-solid fa-share"></i>
      </div>
      <div class="image">
        <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
      </div>
      <p style="font-size: 13px; color: #888; margin-top: -5px;">
    Brand: <strong><?= htmlspecialchars($row['brand']) ?></strong>
  </p>
      <div class="products_text">
        <h2><?= htmlspecialchars($row['name']) ?></h2>
        <p><?= htmlspecialchars(substr($row['description'], 0, 60)) ?>...</p>
        <h3>NPR <?= number_format($row['price'], 2) ?></h3>
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
            $stars = floor($row['rating']);
            for ($i = 0; $i < $stars; $i++) echo '<i class="fa-solid fa-star"></i>';
            for ($i = $stars; $i < 5; $i++) echo '<i class="fa-regular fa-star"></i>';
          ?>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 10px; justify-content: center;">
          <a href="/nepX/pages/product-details.php?id=<?= $row['id'] ?>" class="btn">View</a>
          <button class="btn add-to-cart" data-id="<?= $row['id'] ?>">Add to Cart</button>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?>&sort=<?= $sort ?>&min=<?= $min_price ?>&max=<?= $max_price ?>">&laquo; Prev</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?>&sort=<?= $sort ?>&min=<?= $min_price ?>&max=<?= $max_price ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page + 1 ?>&sort=<?= $sort ?>&min=<?= $min_price ?>&max=<?= $max_price ?>">Next &raquo;</a>
    <?php endif; ?>
  </div>
</section>

<script src="/nepX/js/wishlist.js"></script>
<script src="/nepX/js/cart.js"></script>
</body>
</html>