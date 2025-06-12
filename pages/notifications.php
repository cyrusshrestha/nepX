<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: /nepX/auth/login.php");
  exit();
}
include '../includes/db.php';
include '../includes/nav.php';

// Mark all notifications as read
$userId = $_SESSION['user_id'];
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $userId");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Notifications | Nepal StockX</title>
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
      font-family: 'Instrument Sans', sans-serif;
      background-color: #f4f4f4;
    }
    .notifications {
      max-width: 1000px;
      margin: 50px auto;
      padding: 40px;
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
      font-size: 32px;
    }
    .scroll-container {
      display: flex;
      gap: 20px;
      overflow-x: auto;
      padding: 20px;
      border-radius: 8px;
      scroll-snap-type: x mandatory;
    }
    .scroll-container::-webkit-scrollbar {
      height: 8px;
    }
    .scroll-container::-webkit-scrollbar-thumb {
      background: #6c14d0;
      border-radius: 4px;
    }
    .notification-card {
      min-width: 300px;
      background: #ffffff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      scroll-snap-align: start;
      flex-shrink: 0;
      border-left: 5px solid #c72092;
      transition: 0.3s ease-in-out;
    }
    .notification-card.read {
      border-left-color: #ccc;
      opacity: 0.6;
    }
    .notification-card:hover {
      transform: scale(1.03);
    }
    .notification-card strong {
      color: #6c14d0;
    }
    .notification-card a {
      text-decoration: none;
      color: #333;
    }
    .highlight-bid {
      background: #e3f2fd;
    }
  </style>
</head>
<body>

<section class="notifications">
  <h1>🔔 Your Notifications</h1>
  <div class="scroll-container">

    <?php
    $stmt = $conn->prepare("SELECT message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $notis = $stmt->get_result();

    if ($notis->num_rows > 0):
      while ($row = $notis->fetch_assoc()):
        $highlight = (stripos($row['message'], 'bid') !== false || stripos($row['message'], 'bidding') !== false) ? 'highlight-bid' : '';
    ?>
    <div class="notification-card <?= $row['is_read'] ? 'read' : '' ?> <?= $highlight ?>">
      <p><?= ($row['message']) ?></p>
      <small style="color: #777;">
        <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?>
      </small>
    </div>
    <?php endwhile; else: ?>
    <div class="notification-card">
      <p>No new notifications yet.</p>
    </div>
    <?php endif; ?>

  </div>
</section>

<script src="/nepX/js/wishlist.js"></script>
</body>
</html>