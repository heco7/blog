<?php
// Hämta namnet på den nuvarande sidan/skriptet
$currentPage = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);

// Det aktiva skriptet får en ID-selector som stylas för att markera den besökta sidan i navbaren
?>

<div class="admin-sidebar">
  <nav class="side-nav">
    <ul>
      <li <?php if ($currentPage == 'index') {
          ?> id="sidebar-active" <?php
                                } ?>>
        <div class="icon"><i class="fas fa-tachometer-alt"></i></div><a href="index.php">Instrumentbräda</a>
      </li>
      <li <?php if ($currentPage == 'create_post') {
          ?> id="sidebar-active" <?php
                                } ?>>
        <div class="icon"><i class="fas fa-newspaper"></i></div><a href="create_post.php">Skapa nytt inlägg</a>
      </li>
      <li <?php if ($currentPage == 'edit_post') {
          ?> id="sidebar-active" <?php
                                } ?>>
        <div class="icon"><i class="fas fa-edit"></i></div><a href="edit_post.php">Hantera inlägg</a>
      </li>
      <li <?php if ($currentPage == 'upload') {
          ?> id="sidebar-active" <?php
                                } ?>>
        <div class="icon"><i class="fas fa-file-upload"></i></div><a href="upload.php">Ladda upp bloggbild</a>
      </li>
      <li <?php if ($currentPage == 'edit_image') {
          ?> id="sidebar-active" <?php
                                } ?>>
        <div class="icon"><i class="fas fa-images"></i></div><a href="edit_image.php">Hantera bloggbilder</a>
      </li>
      <li <?php if ($currentPage == 'change_pfp') {
          ?> id="sidebar-active" <?php
                                } ?>>
        <div class="icon"><i class="fas fa-camera"></i></div><a href="change_pfp.php">Lägg till profilbild</a>
      </li>
      <li>
        <div class="icon"><i class="fas fa-pager"></i></div><a href="../view.php?id=<?php echo $_SESSION['id'] ?>">Besök din blogg</a>
      </li>
    </ul>
  </nav>
</div>