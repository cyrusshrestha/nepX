<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "Please log in.";
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (empty($current) || empty($new) || empty($confirm)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: ../pages/profile.php");
    exit();
}

if ($new !== $confirm) {
    $_SESSION['error'] = "New passwords do not match.";
    header("Location: ../pages/profile.php");
    exit();
}

// Fetch user and verify current password
$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($current, $user['password'])) {
    $_SESSION['error'] = "Current password is incorrect.";
    header("Location: ../pages/profile.php");
    exit();
}

// Update password
$hashed = password_hash($new, PASSWORD_BCRYPT);
$update = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$update->bind_param("ss", $hashed, $username);
$update->execute();
$update->close();

$_SESSION['success'] = "✅ Password changed successfully.";
header("Location: ../pages/profile.php");
exit();