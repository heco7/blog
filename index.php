<?php

// Starta session
session_start();
// Databasfunktioner
require_once('includes/db.php');
// Övriga funktioner
require_once('includes/functions.php');

$successMessage = "";

if (isset($_GET['success'])) {
  switch ($_GET['success']) {
    case 'logout':
      $successMessage = '<p class="success-message">Utloggning lyckades!</p>';
      break;
  }
}
// Hämta senaste inläggen och användarna
$conn = db_connect();
$recentPosts = getRecentPosts($conn);
$recentBlogs = getRecentBlogs($conn);
$images = db_select($conn, "SELECT id, filename, description FROM image");
// Spara inläggens bilder
if (!empty($recentPosts) && !empty($images)) {
  foreach ($recentPosts as $blogPost) {
    // Hämta bara bild om inlägget har en bild (dvs ett bild-id finns istället för null)
    if ($blogPost['imageId'] !== null) {
      // Spara bildmetadata och lägg i en assoc array. I arrayen är inläggets ID nyckeln för en array med bildens metadata
      $imageId = $blogPost['imageId'];
      $postId = $blogPost['postId'];
      $image = array();
      foreach ($images as $currentImage) {
        if ($currentImage['id'] == $imageId) {
          $image = $currentImage;
        }
      }
      $images[$postId] = $image;
    }
  }
}
db_disconnect($conn);
?>

<?php require_once('includes/layout/head.php'); ?>
<title>SimpleBlog | Startsida</title>
</head>

<body>
  <?php require_once('includes/layout/header.php') ?>
  <!-- PAGE CONTENT START -->
  <div class="page-content">
    <div class="container grid-2">
      <main class="home-page-content">
        <?php echo $successMessage; ?>
        <div class="welcome has-shadow bg-white">
          <?php if (isset($_SESSION['id'])) { ?>
            <h1>Välkommen <?php echo ucwords(htmlentities($_SESSION['username'])) ?>!</h1>
            <p>För att skapa eller ändra inlägg på din blogg, tryck på "Hantera blogg" i huvudmenyn.</p>
            <p>Nedan kan du se de senaste blogginläggen från våra olika bloggare.</p>

          <?php
          } else { ?>
            <h1>Välkommen!</h1>
            <p>Här kan du skapa en egen blogg eller titta på andras, helt gratis!</p>
            <p>Nedan kan du se de senaste blogginläggen från våra olika bloggare.</p>
          <?php
          } ?>
        </div>
        <hr>
        <section class="recent-posts">
          <?php
          // Rendera de 10 senaste inläggen om det finns några i databasen
          if (!empty($recentPosts)) {
            foreach ($recentPosts as $post) { ?>
              <div class="preview-post has-shadow bg-white">
                <?php
                if (isset($images[$post['postId']])) {
                ?>
                  <img src="<?php
                            echo 'uploads/' . $images[$post['postId']]['filename']; ?>" alt="<?php echo htmlentities($images[$post['postId']]['description']); ?>">
                <?php
                }
                ?>
                <div class="preview-post-meta">
                  <h2 class="preview-post-title"><?php echo (htmlentities($post['title'])) ?></h2>
                  <a class="preview-post-link" href="view.php?id=<?php echo $post['userId'] . "&post=" . $post['postId'] ?>">Visa inlägget</a>
                  <p class="preview-post-info">Skapad av <?php echo ucwords(htmlentities($post['username'])) . " " . htmlentities($post['created']) ?></p>
                </div>
              </div>
          <?php
            }
          } else {
            echo "<p>Det finns inga blogginlägg att visa!</p>";
          }
          ?>
        </section>
      </main>
      <aside class="sidebar has-shadow">
        <section class="recent-blogs">
          <h2>Besök en blogg</h2>
          <?php
          // Rendera de 10 senaste inläggen om det finns några i databasen
          if (!empty($recentBlogs)) {
            foreach ($recentBlogs as $blog) {
              $id = $blog['id'];
              // Spara inläggets titel (upp till 30 tecken)
              $title = substr($blog['title'], 0, 30);
          ?>
              <div class="blog-link-container">
                <a href="view.php?id=<?php echo $id ?>" class="blog-name">
                  <?php echo ucwords(htmlentities($title)) ?>
                </a>
              </div>
            <?php
            }
          } else {
            ?>
            <p>Det finns inga bloggare ännu!</p>
            <a class="normal-link" href="register.php">Skapa en ny blogg</a>
          <?php
          }
          ?>
        </section>
      </aside>
    </div>
  </div>
  <!-- PAGE CONTENT END -->

  <?php require_once('includes/layout/footer.php') ?>
</body>

</html>