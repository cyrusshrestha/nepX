<?php
session_start();
include '../includes/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $action = $_POST['action'] ?? '';

// === SIGNUP ===
if ($action === 'signup') {
  $name = trim($_POST['name']);
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $role = $_POST['role'] ?? 'user';

  if (empty($name) || empty($username) || empty($email) || empty($password)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: /nepX/auth/signup.php");
    exit();
  }

  $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
  
  $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
  $check->bind_param("ss", $username, $email);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    $_SESSION['error'] = "Username or Email already exists.";
    header("Location: /nepX/auth/signup.php");
    exit();
  }

  $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
  $stmt->bind_param("sssss", $name, $username, $email, $hashedPassword, $role);

  if ($stmt->execute()) {
    $_SESSION['success'] = "Signup successful!";
    header("Location: /nepX/auth/login.php");
  } else {
    $_SESSION['error'] = "Signup failed.";
    header("Location: /nepX/auth/signup.php");
  }
  exit();
}

// === LOGIN ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $action = $_POST['action'] ?? '';

  // === LOGIN ===
  if ($action === 'login') {
    $input = trim($_POST['username']); // can be username or email
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($input) || empty($password) || empty($role)) {
      $_SESSION['error'] = "All fields are required.";
      header("Location: /nepX/auth/login.php");
      exit();
    }

    $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);

    if ($isEmail) {
      $stmt = $conn->prepare("SELECT id, name, username, email, password, role FROM users WHERE email = ?");
    } else {
      $stmt = $conn->prepare("SELECT id, name, username, email, password, role FROM users WHERE username = ?");
    }

    $stmt->bind_param("s", $input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
      if ($user['role'] !== $role) {
        $_SESSION['error'] = "❌ Role mismatch.";
      } elseif (!password_verify($password, $user['password'])) {
        $_SESSION['error'] = "❌ Invalid password.";
      } else {
        // ✅ Successful login — set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email']; // ✅ store email
        $_SESSION['role'] = $user['role'];

        // ✅ Redirect based on role
        header("Location: " . ($role === 'admin' ? "/nepX/admin/dashboard.php" : "/nepX/pages/dashboard.php"));
        exit();
      }
    } else {
      $_SESSION['error'] = "❌ User not found.";
    }

    // ⛔ Redirect back to login with error
    header("Location: /nepX/auth/login.php");
    exit();
  }
}



  // === ADD TO WISHLIST ===
  if ($action === 'add_to_wishlist') {
    if (!isset($_SESSION['user_id'])) {
      echo json_encode(['success' => false, 'message' => 'Please login to add to wishlist']);
      exit;
    }

    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);

    $check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
      echo json_encode(['success' => false, 'message' => 'Already in wishlist']);
    } else {
      $insert = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
      $insert->bind_param("ii", $user_id, $product_id);
      if ($insert->execute()) {
        echo json_encode(['success' => true, 'message' => '✅ Added to wishlist']);
      } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
      }
    }
    exit();
  }

  // === ADD TO CART ===
  if ($action === 'add_to_cart') {
    ob_clean(); // Clear anything printed before
    header('Content-Type: application/json');

    try {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login first.']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;
        $is_used = isset($_POST['is_used']) && $_POST['is_used'] == 1 ? 1 : 0;

        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
            exit;
        }

        // Check if already in cart
        $check = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ? AND is_used = ?");
        $check->bind_param("iii", $user_id, $product_id, $is_used);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => true, 'message' => '🛒 Already in cart']);
        } else {
            // Insert
            $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, is_used) VALUES (?, ?, ?, ?)");
            $insert->bind_param("iiii", $user_id, $product_id, $quantity, $is_used);
            $insert->execute();

            // Count total items
            $count = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
            $count->bind_param("i", $user_id);
            $count->execute();
            $total = $count->get_result()->fetch_assoc()['total'] ?? 0;

            echo json_encode([
                'success' => true,
                'message' => '✅ Added to cart!',
                'cart_count' => $total
            ]);
        }
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}
}
?>