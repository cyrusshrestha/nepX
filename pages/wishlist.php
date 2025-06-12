<?php
session_start();
include '../includes/db.php';
include '../admin/dynamic_pricing.php'; // or correct relative path

if (!isset($_SESSION['user_id'])) {
  header("Location: /nepX/auth/login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Handle add to wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
  $product_id = (int) $_POST['product_id'];

  $check = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
  $check->bind_param("ii", $user_id, $product_id);
  $check->execute();
  if ($check->get_result()->num_rows === 0) {
    $insert = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $insert->bind_param("ii", $user_id, $product_id);
    $insert->execute();
  }
  header("Location: wishlist.php");
  exit();
}

// Handle remove from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
  $remove_id = (int) $_POST['remove_id'];
  $remove = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
  $remove->bind_param("ii", $user_id, $remove_id);
  $remove->execute();
  header("Location: wishlist.php");
  exit();
}

// View wishlist items
$query = "SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$products = $stmt->get_result();

include '../includes/nav.php';
?>

<h2 style="text-align:center; margin: 30px 0;">❤️ Your Wishlist</h2>
<div class="box">
  <?php while ($row = $products->fetch_assoc()):
    $img = htmlspecialchars($row['image']);
    $imgPath = "/nepX/uploads/$img";
    $imgFile = $_SERVER['DOCUMENT_ROOT'] . $imgPath;
    if (!file_exists($imgFile)) $imgPath = "/nepX/uploads/placeholder.jpg";
  ?>
  
    <div class="card">
      <div class="image">
        <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($row['name']) ?>">
      </div>
      <div class="products_text">
      <p style="font-size: 13px; color: #888; margin-top: -5px;">
    Brand: <strong><?= htmlspecialchars($row['brand']) ?></strong>
  </p>
        <h2><?= htmlspecialchars($row['name']) ?></h2>
        <p><?= htmlspecialchars(substr($row['description'], 0, 60)) ?>...</p>
        <h3>$<?= number_format($row['price'], 2) ?></h3>
        <a href="/nepX/pages/product-details.php?id=<?= $row['id'] ?>" class="btn">View</a>
        <form action="wishlist.php" method="POST" style="margin-top: 10px;">
          <input type="hidden" name="remove_id" value="<?= $row['id'] ?>">
          <button type="submit" class="btn-outline" onclick="return confirm('Remove from wishlist?')">Remove</button>
        </form>
      </div>
    </div>
  <?php endwhile; ?>
</div>
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
.card {
  padding: 16px;
  border: none;
  border-radius: 20px;
  background: #ffffff;
  text-align: center;
  box-shadow: 0 8px 24px rgba(108, 20, 208, 0.12);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
  justify-content: flex-start; /* key: align content to top */
  height: 100%; /* make cards equal height */
}

.card .image {
  height: 140px;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 8px;
}

.card .image img {
  max-height: 120px;
  object-fit: contain;
  margin: 0;
  display: block;
}

.products_text {
  padding: 0;
  margin-top: 10px;
}

.products_text h2 {
  font-size: 18px;
  color: #222;
  font-weight: 600;
  margin: 6px 0;
}

.products_text p {
  font-size: 13px;
  color: #555;
  margin: 4px 0;
}

.products_text h3 {
  font-size: 15px;
  color: #c72092;
  font-weight: 600;
  margin: 6px 0 12px;
}

.btn, .btn-outline {
  margin-top: 8px;
}
.box {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  padding: 20px 40px 60px;
  max-width: 1200px;
  margin: 0 auto;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/nepX/js/cart.js"></script>