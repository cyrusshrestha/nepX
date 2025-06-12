<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include '../includes/db.php'; // for admin pages
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nepal Stock Shoes</title>
  <link rel="stylesheet" href="/nepX/style.css">  <link rel="shortcut icon" href="image/logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" crossorigin="anonymous" />
  <style>
    /* Reset + Base Styling */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Instrument Sans', sans-serif;
  background-color: #f9f9f9;
  color: #222;
}

/* Section Styling */
#Home {
  width: 100%;
  min-height: 100vh;
  background: linear-gradient(to right, #f8f8f8, #f1ecff);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 60px 20px;
}

/* Main Content */
.main {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.main_content {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  max-width: 1200px;
  justify-content: center;
  align-items: center;
  gap: 40px;
}

/* Text Section */
.main_text h1 {
  font-size: 48px;
  line-height: 1.2;
  color: #6c14d0;
}

.main_text h1 span {
  font-size: 24px;
  color: #c72092;
  font-weight: 600;
  display: block;
  margin-top: 10px;
}

.main_text p {
  font-size: 16px;
  margin-top: 15px;
  color: #444;
  max-width: 500px;
}

/* Image Section */
.main_image img {
  width: 360px;
  max-width: 100%;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}

/* Button */
.button {
  margin-top: 30px;
}

.button a {
  text-decoration: none;
  padding: 12px 24px;
  background: linear-gradient(to right, #c72092, #6c14d0);
  color: #fff;
  font-size: 16px;
  border-radius: 6px;
  font-weight: bold;
  transition: background 0.3s ease;
}

.button a:hover {
  background: #6c14d0;
}

.button i {
  color: #6c14d0;
  margin-left: 10px;
  font-size: 16px;
  vertical-align: middle;
}
</style>
</head>
<body>
  <section id="Home">
    <div class="main">
      <div class="main_content">
        <div class="main_text">
          <h1>NSS <br><span>NEPAL STOCK shoes</span></h1>
          <p>
            Welcome to Nepal Stock Shoes — the best sneaker marketplace. Browse our latest NIKE drops now!
          </p>
          <P>
            please contact on : 9767237317
</P>
        </div>
        <div class="main_image">
          <img src="image/shoes.png" alt="Nike Collection">
        </div>
      </div>
      <div class="button">
      <a href="/nepX/auth/login.php">SHOP NOW</a>        <i class="fa-solid fa-chevron-right"></i>
      </div>
    </div>
  </section>


</body>
</html>