<?php
// uncomment the logo to display it in the nav bar
// fix the username variable to be set to a default value if it is not already set
// add the coffee-table background image to the css file
// load the php data file at the start of your script

?>
<!DOCTYPE html>
<html>

<head>
  <title>Cafe the Hill</title>
  <!-- Add a link to the external stylesheet called styles.css in the head section of your HTML -->
  <link rel="stylesheet" href="css/styles.css">

  <!-- google fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" />

</head>

<body>
  <main class="main-container">
    <nav class="main-nav">
      <!-- <img src="./images/logo.png" alt="School Logo" class="logo"> -->
      <ul class="left-menu">
        <li><a href="index.php">主頁</a></li>
        <li><a href="menu.php">與人對戰</a></li>
        <li><a href="contact.php">與機器對戰</a></li>
      </ul>
      <ul class="right-menu">
        <li>
          <a href="#"><i class="fas fa-search"></i></a>
        </li>
        <li>
          <a href="#"><i class="fas fa-user"></i><span class="user-message"> <?php echo ("username"); ?></span></a>
        </li>
      </ul>
    </nav>
    <!-- showcase -->
    <header class="showcase">
      <h1 class="white">三國嘎</h1>
      <h2>由趙啟閎製作</h2>
      <p>你好！</p>
      <a href="#" class="btn">按此開玩 <i class="fas fa-chevron-right"></i></a>
    </header>
    <div class="content">
      <!-- home cards 1 --->
      <section class="blog-cards">
        <!-- loop the blog cards from the data.php file -->
    </div>

    <!-- coffee on the hill --->
    <section class="coffee-table">
      <div class="content">
        <h2>Cafe On The Hill</h2>
        <p>The best cafe on the hill!</p>
        <p>We are hiring, all students welcome!</p>
        <a href="#" class="btn">Learn More<i class="fas fa-chevron-right"></i></a>
      </div>
    </section>

  </main>

</body>

</html>