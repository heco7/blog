<?php
require_once("includes/admin_functions.php");
require_once("../includes/functions.php");
require_once("../includes/db.php");
require_once("../lib/htmlpurifier-4.13.0/library/HTMLPurifier.auto.php");

session_start();

// Omdirigera om oautentiserad
if (!isset($_SESSION['id'])) {
  redirect("login.php");
}

// Anslut till databas
$conn = db_connect();

// Deklarera variabler
$postTitle = $postContent = $successMessage = $imageId = "";
$userId = $_SESSION['id'];
$imageId = null;
$errors = $images = array();
$images = getImagesByUserId($conn, $userId);

// Försök att publicera inlägg
if (isset($_POST['submit'])) {
  $postTitle = trim($_POST['postTitle']);
  $postContent = trim($_POST['postContent']);

  // Titel saknas
  if (empty($postTitle)) {
    array_push($errors, "Inlägget måste innehålla en titel!");
  }

  // Titel för lång
  if (strlen($postTitle) > 30) {
    array_push($errors, "Bloggtitel för lång, använd max 30 tecken.");
  }

  // Innehåll saknas
  if (empty($postContent)) {
    array_push($errors, "Inlägget saknar innehåll!");
  }

  // Om användaren har valt att inkludera en bild
  if (!empty($_POST['postImage'])) {
    // Spara index för den valda bilden
    $imageId = $_POST['postImage'];
    // Är det ett giltigt ID?
    if (validId($imageId)) {
      // Är användaren ägaren av denna bilden?
      if (!getSingleImageById($conn, $imageId, $_SESSION['id'])) {
        array_push($errors, "Du har inte behörighet att använda denna bild!");
      }
    }
  }

  // Inga fel, skapa inlägg
  if (empty($errors)) {
    // Rensa innehåll med HTMLPurifier innan man skickar in (om någon skulle stänga av JS)
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);
    $postContent = $purifier->purify($postContent);

    // Lägg till inlägg i post-tabellen
    if (createNewPost($conn, $postTitle, $postContent, $userId, $imageId)) {
      $successMessage = "<p class='success-message'>Inlägg skapat!</p>";
      $postTitle = $postContent = "";
    } else {
      array_push($errors, "Fel inträffade, försök igen");
    }
  }
}

// Stäng anslutning till databas
db_disconnect($conn);
?>

<?php require_once("includes/layout/head.php"); ?>
<title>Skapa nytt blogginlägg</title>
</head>

<body>
  <div class="admin-wrapper">
    <?php require_once("includes/layout/header.php"); ?>
    <?php require_once("includes/layout/sidebar.php"); ?>
    <main class="admin-main">
      <div class="page-item has-shadow">
        <form class="admin-form" action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post">
          <?php
          // Rendera eventuella meddelanden
          if ($successMessage) {
            echo $successMessage;
          }
          if (!empty($errors)) {
            renderErrors($errors);
          }
          ?>
          <h1>Skapa ett nytt blogginlägg</h1>
          <div class="form-group">
            <label for="postTitle">
              Inläggets titel
            </label>
            <input class="form-control" type="text" id="postTitle" name="postTitle">
          </div>
          <div class="form-group">
            <p>Huvudbild för blogginlägget. Här kan du välja en av dina uppladdade bilder som kommer användas överst i blogginlägget. (Ej obligatoriskt)</p>
            <button class="btn admin-btn" id="show-img" type="button">Visa bilder</button>
            <div class="user-images">
              <?php
              if (empty($images)) {
                echo "<p>Du har inte laddat upp några bilder för denna användare!</p>";
              } else {
              ?>
                <?php
                foreach ($images as $image) {
                  // https://stackoverflow.com/questions/17541614/use-images-instead-of-radio-buttons/17541916
                  $imagePath = '../uploads/' . $image['filename'];
                ?>
                  <div>
                    <label for="<?php echo htmlentities($image['filename']) ?>">
                      <input type="radio" name="postImage" id="<?php echo htmlentities($image['filename']) ?>" value="<?php echo $image['id'] ?>">
                      <img src="<?php echo $imagePath ?>" alt="">
                      <span class="form-image-desc"><?php echo htmlentities($image['description']) ?></span>
                    </label>
                  </div>
              <?php
                }
              }
              ?>
            </div>
          </div>
          <div class="form-group">
            <label for="postContent">
              Innehåll
            </label>
            <textarea class="form-control" id="postContent" name="postContent"><?php echo $postContent ?></textarea>
          </div>
          <button class="btn admin-btn" type="submit" name="submit">Skapa inlägg</button>

        </form>
      </div>
    </main>
    <?php require_once("includes/layout/footer.php"); ?>
  </div>
  <!-- CKEditor 4 CDN (texteditor) -->
  <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
  <!-- CKEDITOR -->
  <script>
    CKEDITOR.replace('postContent');
  </script>
  <!-- Toggla bilder -->
  <script src="../js/show_images.js"></script>
</body>

</html>