<!-- Header med navigationsmeny -->
<header class="header">
  <div class="container">
    <nav class="top-nav">
      <a class="site-logo" href="index.php">SimpleBlog</a>
      <div class="hamburger-btn" id="hamburger">
        <i class="fas fa-bars fa-3x"></i>
      </div>
      <?php
      // Active navbar: https://stackoverflow.com/questions/39217939/active-navigation-bar-with-php
      // Hämta sökväg för nuvarande fil och extrahera filnamnet
      $currentPage = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
      if (!isset($_SESSION['id'])) {
      ?>
        <ul class="nav-list">
          <li><a <?php if ($currentPage == "index") {
                  ?> id="active" <?php } ?> href="index.php">Startsida</a></li>
          <li><a <?php if ($currentPage == "register") {
                  ?> id="active" <?php } ?> href="register.php">Skapa blogg</a></li>
          <li><a <?php if ($currentPage == "login") {
                  ?> id="active" <?php } ?> href="login.php">Logga in</a></li>
          <li><a <?php if ($currentPage == "terms") {
                  ?> id="active" <?php } ?> href="terms.php">Villkor</a></li>
        </ul>
      <?php
      } else {
      ?>
        <ul class="nav-list">
          <li><a <?php if ($currentPage == "index") {
                  ?> id="active" <?php } ?> href="index.php">Startsida</a></li>
          <li><a href="admin/">Hantera blogg</a></li>
          <li><a <?php if ($currentPage == "login") {
                  ?> id="active" <?php } ?> href="logout.php">Logga ut</a></li>
          <li><a <?php if ($currentPage == "terms") {
                  ?> id="active" <?php } ?> href="terms.php">Villkor</a></li>
        </ul>
      <?php
      } ?>
    </nav>
  </div>
</header>