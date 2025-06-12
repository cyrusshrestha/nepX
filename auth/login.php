<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | Nepal Stock Shoes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="/nepX/style.css" />
  <style>
    .login-container {
  display: flex;
  max-width: 900px;
  margin: 60px auto;
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}

.login-left {
  width: 45%;
  background: linear-gradient(to bottom right, #c72092, #6c14d0);
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 40px 30px;
}

.login-left h1 {
  font-size: 24px;
  margin-bottom: 20px;
}

.login-left img.shoe {
  max-width: 220px;
}

.login-right {
  width: 55%;
  padding: 40px 30px;
}

.login-right h2 {
  text-align: center;
  color: #6c14d0;
  margin-bottom: 20px;
}

form .form-group {
  margin-bottom: 18px;
}

form label {
  display: block;
  margin-bottom: 6px;
  font-weight: bold;
  color: #333;
}

form input, form select {
  width: 100%;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 15px;
}

form button {
  width: 100%;
  padding: 12px;
  background: linear-gradient(to right, #c72092, #6c14d0);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  margin-top: 10px;
}

form button:hover {
  opacity: 0.9;
}

.switch-link {
  text-align: center;
  margin-top: 20px;
  font-size: 14px;
}

.switch-link a {
  color: #c72092;
  text-decoration: none;
  font-weight: bold;
}

.switch-link a:hover {
  text-decoration: underline;
}

.alert-error {
  background: #ffe1e1;
  color: #d60000;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 6px;
  text-align: center;
}
    </style>
</head>
<body>

<div class="login-container">
  <div class="login-left">
    <h1>Nepal Stock Shoes</h1>
    <img src="/nepX/image/shoes.png" alt="Shoe" class="shoe">
  </div>

  <div class="login-right">
    <h2>Welcome Back 👟</h2>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="/nepX/server/controller.php" method="POST">
      <div class="form-group">
        <label for="username">Username or Email</label>
        <input type="text" id="username" name="username" placeholder="Enter your username or email" required />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required />
      </div>

      <div class="form-group">
        <label for="role">Login As</label>
        <select id="role" name="role" required>
          <option value="">-- Select Role --</option>
          <option value="user">Customer</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <button type="submit" name="action" value="login">Login</button>
    </form>

    <div class="switch-link">
      Don’t have an account? <a href="/nepX/auth/signup.php">Sign Up</a>
    </div>
  </div>
</div>

</body>
</html>