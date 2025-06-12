<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up | Nepal Stock Shoes</title>
  <link rel="stylesheet" href="/nepX/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
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
 * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Instrument Sans', sans-serif;
  background: #f4f4f8;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.login-container {
  display: flex;
  width: 750px;
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  transition: 0.3s ease-in-out;
}

.login-left {
  background: linear-gradient(to bottom right, #c72092, #6c14d0);
  color: white;
  padding: 30px 20px;
  width: 40%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.login-left img.logo {
  max-width: 90px;
  margin-bottom: 12px;
}

.login-left img.shoe {
  max-width: 160px;
  margin-top: 10px;
}

.login-left h1 {
  color: #fff;
  margin-top: 10px;
  font-size: 18px;
  font-weight: 600;
  text-align: center;
  line-height: 1.4;
}

.login-right {
  width: 60%;
  padding: 30px 28px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.login-right h2 {
  color: #6c14d0;
  margin-bottom: 20px;
  text-align: center;
}

.alert-error {
  background: #ffe1e1;
  color: #d60000;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 6px;
  text-align: center;
}

form .form-group {
  margin-bottom: 16px;
}

form label {
  display: block;
  margin-bottom: 6px;
  font-weight: bold;
  color: #333;
}

form input,
form select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
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
  opacity: 0.95;
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

/* 🔁 Responsive */
@media (max-width: 768px) {
  .login-container {
    flex-direction: column;
    width: 90%;
  }

  .login-left,
  .login-right {
    width: 100%;
    padding: 25px;
    text-align: center;
  }

  .login-left img.shoe {
    max-width: 120px;
  }

  .login-left h1 {
    font-size: 16px;
  }
}
  </style>
</head>
<body>

<div class="login-container">
<div class="login-left">
  
  <h1 style="margin-top: 15px; font-size: 24px; font-weight: 600; text-align: center;">
    Nepal Stock Shoes
  </h1>
  <img src="/nepX/image/shoes.png" alt="Sneaker" class="shoe">
</div>

  <div class="login-right">
    <h2>Create Account 👟</h2>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="/nepX/server/controller.php" method="POST">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required placeholder="Enter full name">
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required placeholder="Enter email">
      </div>

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required placeholder="Choose a username">
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="Create password">
      </div>

      <div class="form-group">
        <label for="role">Role</label>
        <select name="role" id="role" required>
          <option value="">-- Select Role --</option>
          <option value="user">Customer</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <button type="submit" class="btn" name="action" value="signup">Sign Up</button>
    </form>

    <div class="switch-link">
      Already have an account? <a href="/nepX/auth/login.php">Login</a>
    </div>
  </div>
</div>

</body>
</html>