<?php
session_start();
include '../includes/db.php';
include '../admin/admin-nav.php';
$deletedMessage = null;

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
  $comment_id = intval($_POST['delete_comment_id']);
  $stmt = $conn->prepare("DELETE FROM product_comments WHERE id = ?");
  $stmt->bind_param("i", $comment_id);
  if ($stmt->execute()) {
    $deletedMessage = "Comment deleted successfully!";
  }
}

// Fetch all products with comments
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Product Comments</title>
  <link rel="stylesheet" href="/nepX/assets/css/adminstyle.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
  font-family: 'Instrument Sans', sans-serif;
  background: #f3f4f6;
  margin: 0;
  padding: 20px;
  color: #333;
}

h1 {
  text-align: center;
  color: #6c14d0;
  margin-bottom: 30px;
}

.product {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 40px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
  transition: box-shadow 0.3s ease;
}

.product:hover {
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.product h2 {
  color: #c72092;
  font-size: 22px;
  margin-bottom: 10px;
}

.product p {
  margin: 5px 0;
  line-height: 1.6;
}

h4 {
  margin-top: 20px;
  color: #444;
}

.comment {
  background: #f9fafb;
  border-left: 5px solid #c72092;
  padding: 15px;
  margin-top: 15px;
  border-radius: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.comment p {
  font-size: 16px;
  margin-bottom: 5px;
}

.comment small {
  color: #888;
  font-size: 13px;
}

.delete-btn {
  background: #ff4d4f;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.2s ease;
}

.delete-btn:hover {
  background: #d9363e;
}
  </style>
</head>
<body>

<h1>📝 Product Comments</h1>

<?php while ($product = $products->fetch_assoc()): ?>
  <div class="product">
    <h2><?= htmlspecialchars($product['name']) ?> (ID: <?= $product['id'] ?>)</h2>
    <p><strong>Price:</strong> NPR <?= number_format($product['price'], 2) ?></p>
    <p><strong>Description:</strong> <?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>

    <?php
      $pid = $product['id'];
      $comments = $conn->query("SELECT * FROM product_comments WHERE product_id = $pid ORDER BY created_at DESC");
    ?>

    <?php if ($comments->num_rows > 0): ?>
      <h4>Comments:</h4>
      <?php while ($c = $comments->fetch_assoc()): ?>
        <div class="comment">
          <div>
            <p><strong><?= htmlspecialchars($c['username']) ?>:</strong> <?= htmlspecialchars($c['comment']) ?></p>
            <small><?= date("M d, Y h:i A", strtotime($c['created_at'])) ?></small>
          </div>
          <form method="POST">
            <input type="hidden" name="delete_comment_id" value="<?= $c['id'] ?>">
            <button type="submit" class="delete-btn" onclick="return confirm('Delete this comment?');">Delete</button>
          </form>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No comments for this product.</p>
    <?php endif; ?>
  </div>
<?php endwhile; ?>

<?php if ($deletedMessage): ?>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Deleted!',
      text: '<?= $deletedMessage ?>',
      timer: 2000,
      showConfirmButton: false
    });
  </script>
<?php endif; ?>

</body>
</html>