<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: /nepX/auth/login.php");
  exit();
}
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $model = trim($_POST['model']);
  $brand = trim($_POST['brand']);
  $size = trim($_POST['size']);
  $price = floatval($_POST['price']);
  $category = $_POST['category'];
  $description = trim($_POST['description']);

  // Ensure upload directory exists
  $targetDir = "../uploads/used/";
  if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
  }

  // Unique filenames to avoid overwriting
  $box_img = time() . '_box_' . basename($_FILES['box_image']['name']);
  $shoe_img = time() . '_shoe_' . basename($_FILES['shoe_image']['name']);
  $lace_img = time() . '_lace_' . basename($_FILES['lace_image']['name']);

  // Move uploaded files
  $box_ok = move_uploaded_file($_FILES['box_image']['tmp_name'], $targetDir . $box_img);
  $shoe_ok = move_uploaded_file($_FILES['shoe_image']['tmp_name'], $targetDir . $shoe_img);
  $lace_ok = move_uploaded_file($_FILES['lace_image']['tmp_name'], $targetDir . $lace_img);

  if ($box_ok && $shoe_ok && $lace_ok) {
    $stmt = $conn->prepare("INSERT INTO used_products (user_id, model, brand, size, price, category, description, box_image, shoe_image, lace_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isssdsssss", $user_id, $model, $brand, $size, $price, $category, $description, $box_img, $shoe_img, $lace_img);

    if ($stmt->execute()) {
      $success = "Your product has been submitted for review!";
    } else {
      $error = "Something went wrong. Please try again.";
    }
  } else {
    $error = "File upload failed. Please make sure the folder exists and is writable.";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Sell Used Shoes</title>
  <link rel="stylesheet" href="/nepX/style.css">
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
</head>
<body>

<?php include '../includes/nav.php'; ?>

<div class="auth-container">
  <div class="auth-box" style="max-width: 600px;">
    <h2>Sell Your Used Shoes 👟</h2>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST" enctype="multipart/form-data">
      <input type="text" name="model" placeholder="Shoe Model" required>
      <input type="text" name="brand" placeholder="Brand" required>
      <input type="text" name="size" placeholder="Size" required>
      <input type="number" name="price" placeholder="Price" required step="0.01">
      <select name="category" required>
        <option value="">Select Category</option>
        <option value="Men">Men</option>
        <option value="Women">Women</option>
        <option value="Kid">Kid</option>
      </select>
      <textarea name="description" placeholder="Description" required></textarea>
      <textarea name="description" placeholder="Description" required></textarea>
      <label>Upload Box Image</label>
      <input type="file" name="box_image" accept="image/*" required>

      <label>Upload Shoe Image</label>
      <input type="file" name="shoe_image" accept="image/*" required>

      <label>Upload Shoelace Image</label>
      <input type="file" name="lace_image" accept="image/*" required>

      <button type="submit" class="btn-fill" style="margin-top:15px;">Submit for Review</button>
    </form>
  </div>
</div>

</body>
</html>