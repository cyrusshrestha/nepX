<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: /nepX/auth/login.php");
  exit();
}

include '../includes/db.php';

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
  $stmt->bind_param("i", $id);

  try {
    if ($stmt->execute()) {
      header("Location: manage-users.php?deleted=1");
      exit();
    } else {
      throw new Exception("Deletion failed");
    }
  } catch (mysqli_sql_exception $e) {
    // Show clean error page/message
    echo "<h2 style='color:red; text-align:center; margin-top:40px;'>❌ Cannot delete user: This user has related records (e.g. orders, wishlist, etc.).</h2>";
    echo "<p style='text-align:center;'><a href='manage-users.php'>⬅️ Go Back</a></p>";
  }
} else {
  echo "<h3>❌ Invalid request. Missing user ID.</h3>";
}
?>