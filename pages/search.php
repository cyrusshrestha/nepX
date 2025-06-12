<?php
session_start();
include '../includes/db.php';

$query = $_GET['q'] ?? '';
$stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE CONCAT('%', ?, '%') OR brand LIKE CONCAT('%', ?, '%')");
$stmt->bind_param("ss", $query, $query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Search | Nepal StockX</title>
  <link rel="stylesheet" href="/nepX/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"/>
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
 .box {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  justify-content: center;
  padding: 20px 40px 40px;
  max-width: 1200px;
  margin: 0 auto;
  background: #f9f9f9;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(108, 20, 208, 0.06);
}

.card {
  width: 400px;             /* wider */
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: scale(1.03);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
}

..card img {
  width: 200%;
  height: 100px;
  object-fit: contain;
  display: block;           /* ✅ Removes space below image */
  margin: 0;                /* ✅ Ensures no margin below */
  padding: 0;               /* ✅ Ensures no padding */
  border: none;             /* ✅ Optional, in case of unwanted border */
}

.products_text {
  padding: 14px;
  text-align: center;
}

.products_text h2 {
  font-size: 16px;
  margin-bottom: 6px;
  font-weight: 600;
  color: #333;
}

.products_text p {
  font-size: 13px;
  color: #666;
  margin-bottom: 6px;
}

.products_text h3 {
  color: #c72092;
  font-size: 15px;
  font-weight: 600;
  margin: 8px 0;
}

.btn {
  padding: 8px 14px;
  background-color: #6c14d0;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  text-decoration: none;
  display: inline-block;
  margin-top: 8px;
  transition: background 0.3s ease;
}

.btn:hover {
  background-color: #c72092;
}
  </style>
</head>
<body>

<?php include '../includes/nav.php'; ?>

<div class="search-header">
  🔍 Search Results for: <strong><?= htmlspecialchars($query) ?></strong>
</div>

<div class="box">
<?php
if ($result->num_rows === 0): ?>
  <p style="font-size: 18px; color: #888;">No products found for "<?= htmlspecialchars($query) ?>"</p>
<?php else:
  while ($row = $result->fetch_assoc()):
    $imagePath = "/nepX/uploads/" . $row['image'];
    $serverPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
    if (!file_exists($serverPath)) $imagePath = "/nepX/uploads/placeholder.jpg";
?>
 <p style="font-size: 13px; color: #888; margin-top: -5px;">
    Brand: <strong><?= htmlspecialchars($row['brand']) ?></strong>
  </p>
  <div class="card">
    <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($row['name']) ?>">
    <div class="products_text">
      <h2><?= htmlspecialchars($row['name']) ?></h2>
      <p><?= htmlspecialchars(substr($row['description'], 0, 60)) ?>...</p>
      <h3>NPR <?= number_format($row['price'], 2) ?></h3>
      <a href="/nepX/pages/product-details.php?id=<?= $row['id'] ?>" class="btn">View Product</a>
    </div>
  </div>
<?php endwhile; endif; ?>
</div>

</body>
</html>