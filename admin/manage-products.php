<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: /nepX/auth/login.php");
  exit();
}

include '../includes/db.php';

$msg = "";

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
  $name = $_POST['name'];
  $description = $_POST['description'];
  $price = $_POST['price'];
  $size = $_POST['size'];
  $quantity = $_POST['quantity'];
  $status = $_POST['status'];
  $category = $_POST['category'];
  $image = '';

  if (!empty($_FILES['image']['name'])) {
    $targetDir = "../uploads/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = time() . "_" . basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
      $image = $fileName;
    }
  }

  $stmt = $conn->prepare("INSERT INTO products (name, description, price, size, quantity, status, category, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
  $stmt->bind_param("ssdiisss", $name, $description, $price, $size, $quantity, $status, $category, $image);
  if ($stmt->execute()) $msg = "✅ Product added successfully!";
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'edit') {
  $id = $_POST['id'];
  $name = $_POST['name'];
  $description = $_POST['description'];
  $price = $_POST['price'];
  $size = $_POST['size'];
  $quantity = $_POST['quantity'];
  $status = $_POST['status'];
  $category = $_POST['category'];
  $image = $_POST['existing_image'];

  if (!empty($_FILES['image']['name'])) {
    $targetDir = "../uploads/";
    $fileName = time() . "_" . basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
      $image = $fileName;
    }
  }

  $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, size=?, quantity=?, status=?, category=?, image=? WHERE id=?");
  $stmt->bind_param("ssdiisssi", $name, $description, $price, $size, $quantity, $status, $category, $image, $id);
  if ($stmt->execute()) $msg = "✅ Product updated successfully!";
}

// Handle Delete
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
  $stmt->bind_param("i", $id);
  if ($stmt->execute()) $msg = "🗑️ Product deleted successfully!";
}

// Search Products
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM products WHERE name LIKE '%$search%' OR category LIKE '%$search%' ORDER BY id DESC";
$products = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Products</title>
  <link rel="stylesheet" href="/nepX/admin/adminstyle.css">
  <style>
    body { font-family: Arial, sans-serif; background: #f2f2f2; }
    .dashboard-container { padding: 30px; max-width: 1200px; margin: auto; }
    .form-box, .admin-table { background: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .form-box { padding: 20px; margin-bottom: 30px; }
    .admin-table { width: 100%; border-collapse: collapse; overflow: hidden; }
    .admin-table th, .admin-table td { padding: 12px; border-bottom: 1px solid #eee; }
    .admin-table th { background: #f5f5f5; }
    input, select, textarea { width: 100%; padding: 8px; margin-bottom: 8px; border: 1px solid #ccc; border-radius: 4px; }
    .product-img { width: 60px; border-radius: 6px; }
    button { background: #6c14d0; color: #fff; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; }
    button:hover { background: #4b0e99; }
    a.delete-link { color: #d00; text-decoration: none; margin-left: 10px; }
    .search-bar input { width: 250px; display: inline-block; margin-right: 10px; }
    .alert-success { background: #dff0d8; padding: 12px 20px; border-left: 5px solid #4caf50; color: #3c763d; border-radius: 5px; margin-bottom: 20px; }
  </style>
</head>
<body>

<?php include 'admin-nav.php'; ?>

<section class="dashboard">
  <div class="dashboard-container">
    <h1>📦 Manage Products</h1>

    <?php if ($msg): ?>
      <div class="alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Search Form -->
    <form method="GET" class="search-bar">
      <input type="text" name="search" placeholder="Search by name or category" value="<?= htmlspecialchars($search) ?>">
      <button type="submit">🔍 Search</button>
    </form>

    <!-- Add Form -->
    <form method="POST" enctype="multipart/form-data" class="form-box">
      <h2>Add New Product</h2>
      <input type="hidden" name="action" value="add">
      <input type="text" name="name" placeholder="Name" required>
      <textarea name="description" placeholder="Description" required></textarea>
      <input type="number" step="0.01" name="price" placeholder="Price" required>
      <input type="number" name="size" placeholder="Size" required>
      <input type="number" name="quantity" placeholder="Quantity" required>
      <select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select>
      <select name="category"><option value="Men">Men</option><option value="Women">Women</option><option value="Kids">Kids</option></select>
      <input type="file" name="image" required>
      <button type="submit">➕ Add Product</button>
    </form>

    <!-- Product Table -->
    <table class="admin-table">
      <thead>
        <tr><th>ID</th><th>Image</th><th>Name</th><th>Description</th><th>Price</th><th>Size</th><th>Qty</th><th>Status</th><th>Category</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php while ($p = $products->fetch_assoc()): ?>
        <tr>
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <input type="hidden" name="existing_image" value="<?= $p['image'] ?>">
            <td><?= $p['id'] ?></td>
            <td>
              <?php if (!empty($p['image'])): ?>
                <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" class="product-img"><br>
              <?php endif; ?>
              <input type="file" name="image">
            </td>
            <td><input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>" required></td>
            <td><textarea name="description" required><?= htmlspecialchars($p['description']) ?></textarea></td>
            <td><input type="number" step="0.01" name="price" value="<?= $p['price'] ?>" required></td>
            <td><input type="number" name="size" value="<?= $p['size'] ?>" required></td>
            <td><input type="number" name="quantity" value="<?= $p['quantity'] ?>" required></td>
            <td><select name="status">
              <option value="active" <?= $p['status'] === 'active' ? 'selected' : '' ?>>Active</option>
              <option value="inactive" <?= $p['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select></td>
            <td><select name="category">
              <option value="Men" <?= $p['category'] === 'Men' ? 'selected' : '' ?>>Men</option>
              <option value="Women" <?= $p['category'] === 'Women' ? 'selected' : '' ?>>Women</option>
              <option value="Kids" <?= $p['category'] === 'Kids' ? 'selected' : '' ?>>Kids</option>
            </select></td>
            <td>
              <button type="submit">💾</button>
              <a href="?delete=<?= $p['id'] ?>" class="delete-link" onclick="return confirm('Delete this product?')">🗑️</a>
            </td>
          </form>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

  </div>
</section>
</body>
</html>