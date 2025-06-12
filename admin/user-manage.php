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
  <meta charset="UTF-8" />
  <title>Manage Users</title>
  <link rel="stylesheet" href="/nepX/admin/adminstyle.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      background: #f5f5f5;
      font-family: 'Arial', sans-serif;
    }
    .dashboard-container {
      max-width: 1000px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      color: #6c14d0;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 25px;
    }
    th, td {
      padding: 12px;
      text-align: center;
      border: 1px solid #ddd;
    }
    th {
      background-color: #eee;
      color: #444;
    }
    .btn-edit {
      background: #3498db;
      color: white;
      padding: 6px 14px;
      border-radius: 6px;
      font-weight: bold;
      text-decoration: none;
    }
    .btn-edit:hover {
      background: #2980b9;
    }
  </style>
</head>
<body>

<?php include 'admin-nav.php'; ?>

<section class="dashboard">
  <div class="dashboard-container">
    <h1>👥 User Management</h1>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Username</th>
          <th>Role</th>
          
        </tr>
      </thead>
      <tbody>
        <?php
        $users = $conn->query("SELECT * FROM users ORDER BY id DESC");
        while ($user = $users->fetch_assoc()) {
          echo "<tr>
                  <td>{$user['id']}</td>
                  <td>{$user['name']}</td>
                  <td>{$user['email']}</td>
                  <td>{$user['username']}</td>
                  <td>{$user['role']}</td>
                </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</section>

<?php if (isset($_GET['updated'])): ?>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Updated!',
    text: 'User role updated successfully.',
    timer: 2000,
    showConfirmButton: false
  });
</script>
<?php endif; ?>

</body>
</html>