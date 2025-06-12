<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: /nepX/auth/login.php");
  exit();
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['used_product_id'], $_POST['action'])) {
  $used_product_id = $_POST['used_product_id'];
  $status = $_POST['action'] === 'approve' ? 'approved' : 'rejected';

  $conn->query("UPDATE used_products SET status = '$status' WHERE id = $used_product_id");

  // Notify user
  $result = $conn->query("SELECT user_id FROM used_products WHERE id = $used_product_id");
  $user_id = $result->fetch_assoc()['user_id'];
  $message = $status === 'approved' ? "✅ Your used product has been approved!" : "❌ Your used product has been rejected.";
  $conn->query("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES ($user_id, '$message', 0, NOW())");

  header("Location: manage-used.php");
  exit();
}

// Fetch used products
$filter = $_GET['status'] ?? 'all';
$sql = "SELECT * FROM used_products";
if (in_array($filter, ['approved', 'rejected', 'pending'])) {
  $sql .= " WHERE status = '$filter'";
}
$usedProducts = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Used Products</title>
  <link rel="stylesheet" href="/nepX/admin/adminstyle.css">
  <style>
    .filter-links a {
      margin: 0 10px;
      font-weight: bold;
      color: #007bff;
      text-decoration: none;
    }
    .filter-links a.active {
      text-decoration: underline;
    }
    .modal {
      display: none;
      position: fixed;
      z-index: 10;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.85);
    }
    .modal-content {
      margin: auto;
      display: block;
      max-width: 90%;
      max-height: 90vh;
    }
    .modal-close {
      position: absolute;
      top: 20px;
      right: 40px;
      color: #fff;
      font-size: 40px;
      font-weight: bold;
      cursor: pointer;
    }
  </style>
</head>
<body>

<?php include 'admin-nav.php'; ?>

<section class="dashboard">
  <div class="dashboard-container">
    <h2>♻️ Manage Used Products</h2>

    <div class="filter-links">
      <a href="?status=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a> |
      <a href="?status=pending" class="<?= $filter === 'pending' ? 'active' : '' ?>">Pending</a> |
      <a href="?status=approved" class="<?= $filter === 'approved' ? 'active' : '' ?>">Approved</a> |
      <a href="?status=rejected" class="<?= $filter === 'rejected' ? 'active' : '' ?>">Rejected</a>
    </div>

    <table border="1" cellspacing="0" cellpadding="10" width="100%" style="margin-top: 20px;">
      <tr>
        <th>ID</th>
        <th>User ID</th>
        <th>Model</th>
        <th>Brand</th>
        <th>Price</th>
        <th>Status</th>
        <th>Images</th>
        <th>Action</th>
      </tr>
      <?php while ($row = $usedProducts->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= $row['user_id'] ?></td>
          <td><?= htmlspecialchars($row['model']) ?></td>
          <td><?= htmlspecialchars($row['brand']) ?></td>
          <td>Rs.<?= $row['price'] ?></td>
          <td><?= ucfirst($row['status']) ?></td>
          <td>
            <?php
              foreach (['box_image' => 'Box', 'shoe_image' => 'Shoe', 'lace_image' => 'Lace'] as $img => $label):
              $imgPath = "/nepX/uploads/used/" . $row[$img];
              $imgFile = __DIR__ . "/../uploads/used/" . $row[$img];
              if (!empty($row[$img]) && file_exists($imgFile)):
            ?>
              <img src="<?= $imgPath ?>" width="60" style="cursor:pointer" onclick="openModal(this.src)" alt="<?= $label ?>">
            <?php endif; endforeach; ?>
          </td>
          <td>
            <?php if ($row['status'] === 'pending'): ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="used_product_id" value="<?= $row['id'] ?>">
                <button type="submit" name="action" value="approve">✅ Approve</button>
                <button type="submit" name="action" value="reject">❌ Reject</button>
              </form>
            <?php else: ?>
              <em><?= ucfirst($row['status']) ?></em>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
</section>

<!-- Full-size Image Modal -->
<div id="imageModal" class="modal" onclick="closeModal()">
  <span class="modal-close" onclick="closeModal()">&times;</span>
  <img class="modal-content" id="modalImg">
</div>

<script>
  function openModal(src) {
    document.getElementById('modalImg').src = src;
    document.getElementById('imageModal').style.display = "block";
  }
  function closeModal() {
    document.getElementById('imageModal').style.display = "none";
  }
</script>

</body>
</html>