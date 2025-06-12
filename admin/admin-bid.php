<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: /nepX/auth/login.php");
  exit();
}

include '../includes/db.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $desc = $_POST['description'];
  $price = $_POST['start_price'];
  $image = '';

  if (!empty($_FILES['image']['name'])) {
    $targetDir = "../uploads/bids/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = time() . "_" . basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
      $image = $fileName;
    } else {
      $error = "❌ Failed to upload image.";
    }
  }

  if (empty($error)) {
    $start_time = date('Y-m-d H:i:s');
    $end_time = date('Y-m-d H:i:s', strtotime($start_time . ' +1 day'));

    $stmt = $conn->prepare("INSERT INTO bid_products (name, description, start_price, image, start_time, end_time, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssdsss", $name, $desc, $price, $image, $start_time, $end_time);

    if ($stmt->execute()) {
      $success = "✅ Bidding product added successfully!";
      $conn->query("INSERT INTO notifications (user_id, message, is_read, created_at)
                    SELECT id, '🆕 A new bidding product has been added!', 0, NOW()
                    FROM users WHERE role = 'customer'");
    } else {
      $error = "❌ Database Error: " . $conn->error;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Place Bid Product</title>
  <link rel="stylesheet" href="/nepX/admin/adminstyle.css">
  <style>
    body {
      font-family: 'Instrument Sans', sans-serif;
      background: #f5f5f5;
      margin: 0;
      padding: 0;
    }
    .dashboard-container {
      max-width: 700px;
      margin: 40px auto;
      padding: 30px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    h2 {
      margin-bottom: 20px;
      font-size: 24px;
    }
    form label {
      font-weight: 600;
    }
    form input, form textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-top: 6px;
      margin-bottom: 16px;
      font-size: 14px;
    }
    form button {
      background: #6c14d0;
      color: white;
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
    }
    form button:hover {
      background: #4e0ea3;
    }
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      padding: 15px;
      margin-bottom: 20px;
      border-left: 5px solid #28a745;
      border-radius: 6px;
    }
    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      padding: 15px;
      margin-bottom: 20px;
      border-left: 5px solid #dc3545;
      border-radius: 6px;
    }
  </style>
</head>
<body>

<?php include 'admin-nav.php'; ?>

<div class="dashboard-container">
  <h2>📦 Add New Bidding Product</h2>

  <?php if ($success): ?>
    <div class="alert-success"><?= $success ?></div>
  <?php elseif ($error): ?>
    <div class="alert-error"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <label>Product Name:</label>
    <input type="text" name="name" required>

    <label>Description:</label>
    <textarea name="description" rows="4" required></textarea>

    <label>Start Price (Rs.):</label>
    <input type="number" step="0.01" name="start_price" required>

    <label>Product Image:</label>
    <input type="file" name="image" required>

    <button type="submit">➕ Add Product</button>
  </form>
</div>
  
</body>
</html>