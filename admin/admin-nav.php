<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>

<!-- TOP NAVIGATION BAR -->
<style>
  .admin-topbar {
    background-color: #1e1e2f;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
    font-family: 'Instrument Sans', sans-serif;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    flex-wrap: wrap;
  }

  .admin-topbar .nav-left {
    font-size: 1.8rem;
    font-weight: bold;
    color: #ffffff;
  }

  .admin-topbar .nav-right {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    margin-top: 10px;
  }

  .admin-topbar .nav-right a {
    color: #e0e0e0;
    text-decoration: none;
    font-weight: 500;
    padding: 8px 12px;
    border-radius: 6px;
    transition: background 0.2s ease, color 0.2s ease;
  }

  .admin-topbar .nav-right a:hover {
    background-color: #393857;
    color: #ffffff;
  }

  @media (max-width: 768px) {
    .admin-topbar {
      flex-direction: column;
      align-items: flex-start;
      padding: 20px;
    }

    .admin-topbar .nav-right {
      width: 100%;
      flex-direction: column;
      gap: 10px;
    }
  }
</style>

<div class="admin-topbar">
  <div class="nav-left">NepalStockX Admin</div>
  <div class="nav-right">
    <a href="/nepX/admin/dashboard.php">Dashboard</a>
    <a href="/nepX/admin/manage-orders.php">Manage order</a>
    <a href="/nepX/admin/manage-comments.php">Manage comments</a>
    <a href="/nepX/admin/admin-bid.php">Place Bid Product</a>
    <a href="/nepX/admin/manage-products.php">Manage Products</a>
    <a href="/nepX/admin/manage-used.php">Used Products</a>
    <a href="/nepX/admin/user-manage.php">User </a>
    <a href="/nepX/admin/chart.php">Charts</a>
    <a href="trigger_dynamic_pricing.php">Run Dynamic Pricing</a>
    <a href="/nepX/auth/logout.php">Logout</a>
  </div>
</div>