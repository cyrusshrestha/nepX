<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['username'])) {
    header("Location: /nepX/auth/login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user info
$stmt = $conn->prepare("SELECT name, username, email FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: /nepX/auth/logout.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Profile | Nepal StockX</title>
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
    body {
      background-color: #f4f5f7;
      font-family: Arial, sans-serif;
    }
    .profile-box {
      max-width: 600px;
      margin: 80px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.08);
      text-align: center;
    }
    .profile-box h1 {
      font-size: 28px;
      margin-bottom: 20px;
    }
    .profile-box p {
      margin: 12px 0;
      font-size: 18px;
    }
  </style>
</head>
<body>

<?php include '../includes/nav.php'; ?>


<div class="profile-box">
  <h1>👤 My Profile</h1>
  <?php if (isset($_SESSION['success'])): ?>
  <div style="color: green; font-weight: bold; margin-bottom: 15px;">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
  </div>
<?php elseif (isset($_SESSION['error'])): ?>
  <div style="color: red; font-weight: bold; margin-bottom: 15px;">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
  </div>
<?php endif; ?>
  <p><strong>Full Name:</strong> <?= htmlspecialchars($_SESSION['name'] ?? 'N/A') ?></p>
  <p><strong>Username:</strong> <?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></p>
  <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['email'] ?? 'N/A') ?></p>
  <!-- Password Change Section -->
<div style="margin-top: 40px; text-align: center;">
  <h2>🔒 Change Password</h2>
  <form action="/nepX/auth/change_password.php" method="POST" style="display: inline-block; text-align: left;">
    <label>Current Password</label><br>
    <input type="password" name="current_password" required><br><br>

    <label>New Password</label><br>
    <input type="password" name="new_password" required><br><br>

    <label>Confirm New Password</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <button type="submit">Update Password</button>
  </form>
</div>
</div>
<?php include '../includes/footer.php'; ?>

</body>
</html>