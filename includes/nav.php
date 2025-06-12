<?php
include '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Cart count
$cartCount = 0;
$wishlistCount = 0;
$unreadNotifications = 0;
$notifications = [];

if (isset($_SESSION['user_id'])) {
  $userId = $_SESSION['user_id'];

  // Cart count
  $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $cartResult = $stmt->get_result()->fetch_assoc();
  $cartCount = $cartResult['total'] ?? 0;

  // Wishlist count
  $stmt = $conn->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $wishlistResult = $stmt->get_result()->fetch_assoc();
  $wishlistCount = $wishlistResult['total'] ?? 0;

  // Notification count (unread)
  $stmt = $conn->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $notificationResult = $stmt->get_result()->fetch_assoc();
  $unreadNotifications = $notificationResult['total'] ?? 0;

  // Fetch latest 5 notifications
  $stmt = $conn->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nepal Stock Shoes</title>
  <link rel="stylesheet" href="/nepX/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" crossorigin="anonymous" />
  <link rel="icon" href="/nepX/image/logo.png" />
  <style>
   /* ===================
   General Layout
=================== */
.top-icons {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-left: 15px;
}

.top-icons i {
  font-size: 20px;
  color: #6c14d0;
  transition: transform 0.2s ease, color 0.2s ease;
  cursor: pointer;
}

.top-icons i:hover {
  color: #c72092;
  transform: scale(1.1);
}

/* ===================
   Notification Icon
=================== */
.notif-wrapper {
  position: relative;
  display: inline-block;
}

.notif-wrapper i.fa-bell {
  color: #6c14d0;
}

.notif-count {
  position: absolute;
  top: -6px;
  right: -10px;
  background: red;
  color: white;
  font-size: 12px;
  padding: 2px 6px;
  border-radius: 50%;
  font-weight: bold;
}

.notif-dropdown {
  position: absolute;
  top: 30px;
  right: 0;
  width: 300px;
  max-height: 350px;
  overflow-y: auto;
  background: #fff;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  display: none;
  z-index: 999;
}

.notif-dropdown.active {
  display: block;
}

.notif-item {
  padding: 10px;
  font-size: 14px;
  border-bottom: 1px solid #eee;
}

.notif-item small {
  color: #888;
  display: block;
  font-size: 12px;
}

/* ===================
   Profile Icon
=================== */
.profile-wrapper {
  position: relative;
  display: inline-block;
  margin-left: 15px;
}

.profile-wrapper i.fa-user {
  color: #6c14d0;
}

.profile-dropdown {
  display: none;
  position: absolute;
  top: 28px;
  right: 0;
  width: 200px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  z-index: 999;
}

.profile-dropdown.active {
  display: block;
}

.profile-dropdown a {
  display: block;
  padding: 10px 14px;
  text-decoration: none;
  font-size: 14px;
  color: #6c14d0;
  font-weight: 500;
  border-bottom: 1px solid #eee;
  transition: background 0.2s ease, color 0.2s ease;
}

.profile-dropdown a:hover {
  background: #f5f5f5;
  color: #c72092;
}

/* ===================
   Cart & Wishlist Counters
=================== */
.icons a {
  position: relative;
  margin-right: 10px;
}

.cart-count,
.wishlist-count,
.notif-count {
  position: absolute;
  top: -8px;
  right: -10px;
  background: red;
  color: white;
  font-size: 11px;
  border-radius: 50%;
  padding: 2px 5px;
}

/* ===================
   Search Bar
=================== */
.nav-search-form {
  display: flex;
  align-items: center;
  margin-left: 20px;
  background: white;
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
}

.nav-search-form input[type="text"] {
  padding: 8px 12px;
  border: none;
  outline: none;
  font-size: 15px;
  width: 180px;
}

.nav-search-form button {
  background-color: #6c14d0;
  color: white;
  border: none;
  padding: 10px 16px;
  font-size: 16px;
  cursor: pointer;
}

.nav-search-form button:hover {
  background-color: #c72092;
}
.nav-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #fff;
  padding: 10px 30px;
  border-radius: 16px;
  box-shadow: 0 6px 12px rgba(108, 20, 208, 0.15);
  margin: 20px auto;
  max-width: 1250px;
  flex-wrap: wrap;
  gap: 20px;
}

/* Responsive breakdown */
.nav-left, .nav-center, .nav-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.nav-center a {
  text-decoration: none;
  color: #6c14d0;
  font-weight: 500;
  padding: 6px 10px;
  transition: 0.2s ease;
  font-size: 14px;
}

.nav-center a:hover {
  color: #c72092;
  background: #f2f2f2;
  border-radius: 6px;
}

.logo {
  font-weight: 800;
  font-size: 22px;
  background: linear-gradient(to right, #c72092, #6c14d0);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-right: 10px;
}
.full-logo-banner {
  width: 100%;
  background: linear-gradient(to right, #c72092, #6c14d0);
  padding: 14px 0;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.full-logo-text {
  margin: 0;
  font-size: 30px;
  font-weight: 800;
  font-family: 'Instrument Sans', sans-serif;
  color: white;
  letter-spacing: 1px;
}
  </style>
</head>
<body>
<div class="full-logo-banner">
  <h1 class="full-logo-text">Nepal stock shoes</h1>
</div>
<nav>
  <form action="/nepX/pages/search.php" method="GET" class="nav-search-form">
  <input type="text" name="q" placeholder="Search shoes..." required>
  <button type="submit"><i class="fa fa-search"></i></button>
  </form>
  <ul class="nav-links">
    <li><a href="/nepX/pages/dashboard.php">Home</a></li>
    <li><a href="/nepX/pages/trending.php">Trending</a></li>
    <li><a href="/nepX/pages/new-products.php">New Arrivals</a></li>
    <li><a href="/nepX/pages/Men.php">Men</a></li>
    <li><a href="/nepX/pages/Women.php">Women</a></li>
    <li><a href="/nepX/pages/kid.php">Child</a></li>
    <li><a href="/nepX/pages/sell-used.php">Sell</a></li>
    <li><a href="/nepX/pages/used.php">Used Product</a></li>
    <li><a href="/nepX/pages/bid.php">unique product</a></li>
    <li><a href="/nepX/pages/orders.php">myorder</a></li>
  </ul>

  <div class="icons">
    <a href="/nepX/pages/wishlist.php"><i class="fa-solid fa-heart"></i>
      <?php if ($wishlistCount > 0): ?><span class="wishlist-count"><?= $wishlistCount ?></span><?php endif; ?>
    </a>

    <a href="/nepX/pages/cart.php"><i class="fa-solid fa-cart-shopping"></i>
      <?php if ($cartCount > 0): ?><span class="cart-count"><?= $cartCount ?></span><?php endif; ?>
    </a>

    <div class="notif-wrapper">
      <i class="fa-solid fa-bell" onclick="toggleNotif()"></i>
      <?php if ($unreadNotifications > 0): ?><span class="notif-count"><?= $unreadNotifications ?></span><?php endif; ?>

      <div class="notif-dropdown" id="notif-dropdown">
        <?php foreach ($notifications as $n): ?>
          <div class="notif-item">
            <?= htmlspecialchars_decode($n['message']) ?>
            <small><?= date('M d, Y h:i A', strtotime($n['created_at'])) ?></small>
          </div>
        <?php endforeach; ?>
        <div class="notif-item" style="text-align: center;"><a href="/nepX/pages/notifications.php">View All</a></div>
      </div>
    </div>
  </div>

  <div class="auth-btn">
    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="profile-wrapper">
  <i class="fa-solid fa-user" onclick="toggleProfileDropdown()" style="font-size: 20px; cursor: pointer;"></i>

  <div class="profile-dropdown" id="profile-dropdown">
    <a href="/nepX/pages/profile.php">👤 View Profile</a>
    <a href="/nepX/auth/change_password.php">🔒 Change Password</a>
    <a href="/nepX/auth/logout.php">🚪 Logout</a>
  </div>
</div>
     
    <?php else: ?>
      <a href="/nepX/auth/login.php" class="btn-outline">Login</a>
      <a href="/nepX/auth/signup.php" class="btn-fill">Sign Up</a>
    <?php endif; ?>
  </div>
</nav>

<script>
  function toggleNotif() {
    document.getElementById('notif-dropdown').classList.toggle('active');
  }

  window.onclick = function(e) {
    if (!e.target.closest('.notif-wrapper')) {
      document.getElementById('notif-dropdown')?.classList.remove('active');
    }
  }
</script>
<script>
  function toggleProfileDropdown() {
    document.getElementById("profile-dropdown").classList.toggle("active");
  }

  // Close if clicked outside
  window.onclick = function(e) {
    if (!e.target.closest('.profile-wrapper')) {
      document.getElementById('profile-dropdown')?.classList.remove('active');
    }
    if (!e.target.closest('.notif-wrapper')) {
      document.getElementById('notif-dropdown')?.classList.remove('active');
    }
  };
</script>

</body>
</html>