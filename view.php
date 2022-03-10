<?php
// Starta session
session_start();
// Databasfunktioner
require_once('includes/db.php');
// Övriga funktioner
require_once('includes/functions.php');

// Anslut till databasen
$conn = db_connect();

// Deklarera variabler
$user = $posts = $currentPost = array();
$userId = $postId = $username = $title = $presentation = $profilePicturePath = "";

// Om GET-parameter med användarID finns
if (isset($_GET['id'])) {
  $userId = $_GET['id'];
  // Kolla om det är ett giltigt ID
  if (validId($userId)) {
    // Försök att hämta användardata
    $user = getUserById($conn, $userId);
    // Om användaren finns, spara data i variabler
    if ($user) {
      $username = $user['username'];
      $title = $user['title'];
      $presentation = $user['presentation'];
      $profilePicturePath = getProfilePicture($conn, $userId);
      $posts = getPostsByUserId($conn, $userId);
    }
  }
}
// Om både användarID och postID finns i URI
if (isset($_GET['id']) && isset($_GET['post'])) {
  $postId = $_GET['post'];
  // Kolla om båda IDs är giltiga
  if (validId($userId) && validId($postId)) {
    // Kolla om användaren finns
    if ($user) {
      // Försök att hämta inlägget
      $currentPost = findPostById($posts, $postId);
    }
  }
}
?>
<?php require_once('includes/layout/head.php'); ?>
<title>SimpleBlog | <?php echo htmlentities($title) ?></title>
</head>

<body>
  <?php require_once('includes/layout/header.php') ?>
  <!-- PAGE CONTENT START -->
  <div class="page-content">
    <div class="blog-page-container has-shadow">
      <?php require_once('includes/layout/menu.php') ?>
      <?php require_once('includes/layout/content.php') ?>
      <?php require_once('includes/layout/info.php') ?>
    </div>
  </div>
  <!-- PAGE CONTENT END -->

  <?php require_once('includes/layout/footer.php') ?>
  <?php
  // Stäng koppling till databasen
  db_disconnect($conn);
  ?>
</body>

</html>