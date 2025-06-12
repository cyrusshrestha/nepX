<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nepal Stock Shoes</title>
  <link rel="stylesheet" href="style.css">
  <link rel="shortcut icon" href="image/logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" crossorigin="anonymous" />
</head>
<body>

  <?php include 'includes/header.php'; ?>

  <section id="Home">
    <div class="main">
      <div class="main_content">
        <div class="main_text">
          <h1>NIKE<br><span>Collection</span></h1>
          <p>
            Welcome to Nepal Stock Shoes — the best sneaker marketplace. Browse our latest NIKE drops now!
          </p>
        </div>
        <div class="main_image">
          <img src="image/shoes.png" alt="Nike Collection">
        </div>
      </div>
      <div class="button">
      <a href="#" class="btn add-to-cart" data-id="<?= $product['id'] ?>">Add to Cart</a>
        <a href="#Products">SHOP NOW</a>
        <i class="fa-solid fa-chevron-right"></i>
      </div>
    </div>
  </section>
  <?php include 'includes/footer.php'; ?>

</body>
</html>
