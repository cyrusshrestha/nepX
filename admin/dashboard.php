<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: /nepX/auth/login.php");
  exit();
}
include '../includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f6f8fc;
      margin: 0;
      padding: 0;
    }

    .dashboard-container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 20px;
    }

    h1 {
      font-size: 28px;
      color: #222;
      margin-bottom: 30px;
    }

    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }

    .card {
      background: linear-gradient(145deg, #ffffff, #f1f1f1);
      border-radius: 16px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 6px 20px rgba(0,0,0,0.06);
      transition: transform 0.2s ease;
    }
    .card:hover {
      transform: translateY(-5px);
    }

    .card h2 {
      font-size: 18px;
      color: #666;
      margin-bottom: 10px;
    }

    .card p {
      font-size: 32px;
      font-weight: bold;
      color: #6c14d0;
    }

    .section {
      background: #ffffff;
      border-radius: 12px;
      padding: 25px 20px;
      margin-bottom: 30px;
      box-shadow: 0 4px 14px rgba(0,0,0,0.05);
    }

    .section h3 {
      font-size: 18px;
      margin-bottom: 15px;
      color: #333;
      border-left: 4px solid #6c14d0;
      padding-left: 10px;
    }

    .section ul {
      padding-left: 20px;
      line-height: 1.8;
      font-size: 15px;
      color: #555;
    }

    .quick-links {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .quick-links a {
      background: #6c14d0;
      color: #fff;
      padding: 10px 18px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      transition: background 0.2s;
    }

    .quick-links a:hover {
      background: #4b0e99;
    }
    /* Chart section wrapper */
    .chart-section {
  background: #fff;
  border-radius: 12px;
  padding: 25px 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  max-width: 960px;
  margin: 40px auto;
}

.chart-section h3 {
  text-align: center;
  margin-bottom: 20px;
  color: #6c14d0;
  font-size: 20px;
}

.chart-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 30px;
}

.chart-container {
  background: #fafafa;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  width: 380px;
  height: 320px;
}
canvas {
  width: 100% !important;
  height: auto !important;
}
.chart-container canvas {
  max-width: 50%;
  max-height: 200px;
  object-fit: contain;
}
  </style>
</head>
<body>

<?php include __DIR__ . '/admin-nav.php'; ?>

<section class="dashboard">
  <div class="dashboard-container">
    <h1>👋 Welcome Admin</h1>
    <div class="dashboard-cards">
      <div class="card">
        <h2>Total Products</h2>
        <p><?= $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total']; ?></p>
      </div>
      <div class="card">
        <h2>Used Products</h2>
        <p><?= $conn->query("SELECT COUNT(*) as total FROM used_products")->fetch_assoc()['total']; ?></p>
      </div>
      <div class="card">
        <h2>Total Users</h2>
        <p><?= $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total']; ?></p>
      </div>
      <div class="card">
        <h2>Active Bids</h2>
        <p><?= $conn->query("SELECT COUNT(*) as total FROM bid_products WHERE end_time > NOW()")->fetch_assoc()['total']; ?></p>
      </div>
    </div>
    <div class="section">
  <h3>📊 Insights</h3>
  <canvas id="roleChart" height="140"></canvas>
  <canvas id="productChart" height="140" style="margin-top: 40px;"></canvas>
</div>

<?php
  // Get role counts
  $userData = $conn->query("SELECT role, COUNT(*) as total FROM users GROUP BY role");
  $roles = [];
  $roleCounts = [];
  while ($row = $userData->fetch_assoc()) {
    $roles[] = ucfirst($row['role']);
    $roleCounts[] = $row['total'];
  }

  // Product counts
  $newProducts = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
  $usedProducts = $conn->query("SELECT COUNT(*) as total FROM used_products")->fetch_assoc()['total'];
?>

<!-- ✅ Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  const roleChart = document.getElementById('roleChart');
  new Chart(roleChart, {
    type: 'pie',
    data: {
      labels: <?= json_encode($roles) ?>,
      datasets: [{
        label: 'User Roles',
        data: <?= json_encode($roleCounts) ?>,
        backgroundColor: ['#6c14d0', '#c72092'],
        borderWidth: 1
      }]
    },
    options: {
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });

  const productChart = document.getElementById('productChart');
  new Chart(productChart, {
    type: 'bar',
    data: {
      labels: ['New Products', 'Used Products'],
      datasets: [{
        label: 'Product Count',
        data: [<?= $newProducts ?>, <?= $usedProducts ?>],
        backgroundColor: ['#6c14d0', '#f39c12']
      }]
    },
    options: {
      scales: {
        y: { beginAtZero: true }
      },
      plugins: {
        legend: { display: false }
      }
    }
  });
</script>
    
    </div>

    <div class="section">
      <h3>ℹ️ Dashboard Overview</h3>
      <ul>
        <li>Track and manage live bid activities.</li>
        <li>Monitor registered users and sellers.</li>
        <li>Quickly access product listings and charts.</li>
        <li>More analytics available under "Charts" section.</li>
      </ul>
    </div>
  </div>
</section>

</body>
</html>