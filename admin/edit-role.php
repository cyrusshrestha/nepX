<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: /nepX/auth/login.php");
  exit();
}

if (!isset($_GET['id'])) {
  echo "User ID not provided.";
  exit();
}

$user_id = intval($_GET['id']);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_role = $_POST['role'];
  $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
  $stmt->bind_param("si", $new_role, $user_id);
  $stmt->execute();
  header("Location: manage-users.php?updated=1");
  exit();
}

// Fetch user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
  echo "User not found.";
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Edit User Role</title>
  <link rel="stylesheet" href="/nepX/admin/adminstyle.css">
  <style>
    .edit-box {
      max-width: 500px;
      margin: 80px auto;
      padding: 30px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    }
    h2 {
      color: #6c14d0;
      text-align: center;
      margin-bottom: 20px;
    }
    label {
      font-weight: bold;
      display: block;
      margin-bottom: 8px;
    }
    select {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      font-size: 16px;
      border: 1px solid #ccc;
      margin-bottom: 20px;
    }
    .btn-save {
      background: linear-gradient(to right, #c72092, #6c14d0);
      color: white;
      padding: 12px 20px;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
    }
    .btn-save:hover {
      opacity: 0.9;
    }
  </style>
</head>
<body>

<div class="edit-box">
  <h2>✏️ Edit Role for <span style="color:#444"><?= htmlspecialchars($user['username']) ?></span></h2>

  <form method="POST">
    <label for="role">User Role</label>
    <select name="role" id="role" required>
      <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Customer</option>
      <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select>

    <button type="submit" class="btn-save">Save Changes</button>
  </form>
</div>

</body>
</html>