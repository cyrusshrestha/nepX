<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: /nepX/auth/login.php");
  exit();
}
include '../includes/db.php';

// Orders per hour
$orderHourly = $conn->query("SELECT HOUR(created_at) AS hr, COUNT(*) AS count FROM orders GROUP BY hr ORDER BY hr ASC");
$hours = []; $orderCounts = [];
while ($r = $orderHourly->fetch_assoc()) {
  $hours[] = $r['hr'] . ":00";
  $orderCounts[] = $r['count'];
}

// Active users per day
$userActive = $conn->query("SELECT DATE(created_at) AS day, COUNT(*) AS active FROM users GROUP BY day ORDER BY day DESC LIMIT 7");
$days = []; $activeUsers = [];
while ($r = $userActive->fetch_assoc()) {
  $days[] = $r['day'];
  $activeUsers[] = $r['active'];
}

// Approved vs Rejected Used Products
$usedApproved = $conn->query("SELECT COUNT(*) FROM used_products WHERE status='approved'")->fetch_row()[0];
$usedRejected = $conn->query("SELECT COUNT(*) FROM used_products WHERE status='rejected'")->fetch_row()[0];

// Total Bids vs Wishlist
$bidCount = $conn->query("SELECT COUNT(*) FROM product_bids")->fetch_row()[0];
$wishlistCount = $conn->query("SELECT COUNT(*) FROM wishlist")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Analytics | NepalStockX</title>
  <link rel="stylesheet" href="/nepX/admin/adminstyle.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; }
    .dashboard-container { max-width: 1200px; margin: auto; padding: 30px; }
    .chart-box {
      margin-bottom: 40px;
      background: #fff;
      padding: 20px 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    canvas { display: block; max-width: 420px; height: 300px; margin: auto; }
    h2 { text-align: center; margin-bottom: 10px; }
    .chart-desc { text-align: center; color: #555; font-size: 14px; margin-bottom: 20px; }
  </style>
</head>
<body>

<?php include 'admin-nav.php'; ?>

<section class="dashboard">
  <div class="dashboard-container">
    <h1>📊 NepalStockX Admin Charts</h1>

    <!-- 1. Orders Per Hour -->
    <div class="chart-box">
      <h2>🕒 Orders Per Hour</h2>
      <p class="chart-desc">Shows the number of orders placed each hour of the day. Useful to identify peak buying times.</p>
      <canvas id="orderChart"></canvas>
    </div>

    <!-- 2. Active Users Per Day -->
    <div class="chart-box">
      <h2>👤 Daily Active Users (Last 7 Days)</h2>
      <p class="chart-desc">Tracks how many users signed up or logged activity each day over the past week.</p>
      <canvas id="activeUsersChart"></canvas>
    </div>

    <!-- 3. Used Product Approval -->
    <div class="chart-box">
      <h2>✅ Used Product Approval Rate</h2>
      <p class="chart-desc">Displays the percentage of used products approved vs rejected by the admin team.</p>
      <canvas id="usedProductChart"></canvas>
    </div>

    <!-- 4. Total Bids vs Wishlist -->
    <div class="chart-box">
      <h2>📌 Bids vs Wishlist</h2>
      <p class="chart-desc">Compares how many users are bidding vs how many have wishlist activity on the platform.</p>
      <canvas id="summaryChart"></canvas>
    </div>

  </div>
</section>

<script>
const orderChart = new Chart(document.getElementById('orderChart').getContext('2d'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($hours) ?>,
    datasets: [{
      label: 'Orders',
      data: <?= json_encode($orderCounts) ?>,
      backgroundColor: '#4caf50'
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});

const activeUsersChart = new Chart(document.getElementById('activeUsersChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: <?= json_encode(array_reverse($days)) ?>,
    datasets: [{
      label: 'Active Users',
      data: <?= json_encode(array_reverse($activeUsers)) ?>,
      backgroundColor: 'rgba(33,150,243,0.2)',
      borderColor: '#2196f3',
      tension: 0.3,
      fill: true
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});

const usedProductChart = new Chart(document.getElementById('usedProductChart').getContext('2d'), {
  type: 'doughnut',
  data: {
    labels: ['Approved', 'Rejected'],
    datasets: [{
      data: [<?= $usedApproved ?>, <?= $usedRejected ?>],
      backgroundColor: ['#4caf50', '#f44336']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      datalabels: {
        formatter: (value, ctx) => {
          const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
          return ((value / total) * 100).toFixed(1) + '%';
        },
        color: '#fff',
        font: { weight: 'bold' }
      },
      legend: { position: 'bottom' }
    }
  },
  plugins: [ChartDataLabels]
});

const summaryChart = new Chart(document.getElementById('summaryChart').getContext('2d'), {
  type: 'doughnut',
  data: {
    labels: ['Total Bids', 'Total Wishlist'],
    datasets: [{
      data: [<?= $bidCount ?>, <?= $wishlistCount ?>],
      backgroundColor: ['#03a9f4', '#ff5722']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      datalabels: {
        formatter: (value, ctx) => {
          const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
          return ((value / total) * 100).toFixed(1) + '%';
        },
        color: '#fff',
        font: { weight: 'bold' }
      },
      legend: { position: 'bottom' }
    }
  },
  plugins: [ChartDataLabels]
});
</script>

</body>
</html>