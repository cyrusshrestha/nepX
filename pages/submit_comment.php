<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /nepX/auth/login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$rating = intval($_POST['rating']);
$comment = trim($_POST['comment']);

// Insert rating
$ratingStmt = $conn->prepare("INSERT INTO product_ratings (user_id, product_id, rating) VALUES (?, ?, ?)");
$ratingStmt->bind_param("iii", $user_id, $product_id, $rating);
$ratingStmt->execute();

// Insert comment
$commentStmt = $conn->prepare("INSERT INTO product_comments (user_id, product_id, comment) VALUES (?, ?, ?)");
$commentStmt->bind_param("iis", $user_id, $product_id, $comment);
$commentStmt->execute();

header("Location: product-details.php?id=$product_id");
exit();
?>