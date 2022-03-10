<header class="admin-header">
  <a href="../index.php"><i class="fas fa-arrow-left back-icon"></i>Tillbaka till startsidan</a>
  <p class="logged-in-user">Inloggad som <?php echo ucwords(htmlentities($_SESSION['username'])); ?></p>
  <a href="../logout.php">Logga ut</a>
</header>