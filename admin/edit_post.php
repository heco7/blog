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

// Deklarera variabler
$isEditing = false;
$postId = $successMessage = "";
$imageId = null;
$posts = $postToEdit = $errors = array();

// Anslut till databas
$conn = db_connect();

// Ta bort valda inlägget
if (isset($_POST['deletePost'])) {
  $postId = $_POST['postId'];

  // Är det ett korrekt ID?
  if (validId($postId)) {
    // Innan inlägget tas bort, bekräfta att det existerar och att användaren för denna session är ägaren
    if (getSinglePostFromUser($conn, $postId, $_SESSION['id'])) {
      if (deletePostById($conn, $postId, $_SESSION['id'])) {
        $successMessage = "<p class='success-message'>Inlägget har tagits bort!</p>";
      } else {
        array_push($errors, "Fel vid borttagning. Försök igen");
      }
    } else {
      array_push($errors, "Fel vid borttagning. Försök igen");
    }
  } else {
    array_push($errors, "Fel inträffade");
  }
}
// När användaren trycker på redigeringsknappen
else if (isset($_POST['editPost'])) {
  $postId = $_POST['postId'];
  if (validId($postId)) {
    // Innan inlägget redigeras, bekräfta att det existerar och att användaren för denna session är ägaren
    if ($postToEdit = getSinglePostFromUser($conn, $postId, $_SESSION['id'])) {
      $isEditing = true;
      $_SESSION['postId'] = $postToEdit['id'];
      $postId = $postToEdit['id'];
      $postTitle = $postToEdit['title'];
      $postContent = $postToEdit['content'];
      $postImage = $postToEdit['imageId'];
    } else {
      array_push($errors, "Fel inträffade. Försök igen");
    }
  } else {
    array_push($errors, "Fel inträffade. Försök igen");
  }
}

// Uppdatera inlägg
else if (isset($_POST["updatePost"])) {
  $isEditing = true;
  $postTitle = trim($_POST['postTitle']);
  $postContent = trim($_POST['postContent']);
  $postId = $_SESSION['postId'];

  // Tomma fält
  if (empty($postTitle)) {
    array_push($errors, "Inlägget måste innehålla en titel!");
  }

  if (empty($postContent)) {
    array_push($errors, "Inlägget saknar innehåll!");
  }

  // Om användaren har valt att inkludera en bild
  if (!empty($_POST['postImage'])) {
    // Spara index för den valda bilden
    $imageId = $_POST['postImage'];
    // Är det ett giltigt ID?
    if (validId($imageId)) {
      // Är användaren ägaren av denna bild?
      if (!getSingleImageById($conn, $imageId, $_SESSION['id'])) {
        array_push($errors, "Fel inträffade, försök igen");
      }
    }
  }

  // Uppdatera inlägget och omdirigera
  if (empty($errors)) {
    // Rensa innehåll med HTMLPurifier innan man skickar in (om någon skulle stänga av JS)
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);
    $postContent = $purifier->purify($postContent);

    if (updatePost($conn, $postTitle, $postContent, $imageId, $postId)) {
      redirect("admin/edit_post.php?success=postedit");
    } else {
      array_push($errors, "Fel inträffade, försök igen");
    }
  }
}

// Hämta bloggarens inlägg
$posts = getPostsByUserId($conn, $_SESSION['id']);

// Hämta metadata för användarens bilder vid redigering
if ($isEditing) {
  $images = getImagesByUserId($conn, $_SESSION['id']);
}

// Stäng anslutning till databas
db_disconnect($conn);

?>

<?php require_once("includes/layout/head.php"); ?>
<title>Hantera blogginlägg</title>
</head>

<body>
  <div class="admin-wrapper">
    <?php require_once("includes/layout/header.php"); ?>
    <?php require_once("includes/layout/sidebar.php"); ?>
    <main class="admin-main">
      <div class="page-item has-shadow">
        <?php
        // Rendera eventuella meddelanden
        if (isset($_GET['success'])) {
          if ($_GET['success'] === "postedit") {
            $successMessage = "<p class='success-message'> Inlägget har redigerats!</p>";
          }
        }
        echo $successMessage;
        ?>
        <?php
        // Inlägg finns och ej i redigeringsläge
        if (!empty($posts) && !$isEditing) {

          // Rendera felmeddelanden
          if (!empty($errors)) {
            renderErrors($errors);
          }
        ?>
          <h1>Redigera eller ta bort blogginlägg</h1>
          <?php
          foreach ($posts as $post) {
            $title = htmlentities($post['title']);
            $postId = $post['id'];
          ?>
            <form class="edit-post-form" action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post">
              <input type="hidden" name="postId" value="<?php echo $postId ?>">
              <div class="edit-post">
                <div class="title">
                  <p><?php echo $title ?></p>
                </div>
                <div class="buttons">
                  <div class="option">
                    <button type="submit" class="btn edit-post-btn admin-btn" name="editPost">Redigera</button>
                  </div>
                  <div class="option">
                    <button type="submit" class="btn edit-post-btn admin-btn" name="deletePost">Ta bort</button>
                  </div>
                </div>
              </div>
            </form>
          <?php
          }
        }
        // Redigerar inlägg
        else if ($isEditing) {
          ?>
          <form class="admin-form" action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post">
            <input type="hidden" name="postId" value="<?php echo $postId ?>">
            <a class="edit-post-back" href="edit_post.php"><i class="fas fa-arrow-left"></i>Gå tillbaka</a>
            <h1>Redigera inlägg</h1>
            <?php
            // Rendera felmeddelanden
            if (!empty($errors)) {
              renderErrors($errors);
            }
            ?>
            <div class="form-group">
              <label for="postTitle">
                Inläggets titel
              </label>
              <input class="form-control" type="text" id="postTitle" name="postTitle" value="<?php echo htmlentities($postTitle) ?>">
            </div>
            <div class="form-group">
              <p>Inläggets huvudbild (lämna tom om du vill behålla gamla bilden)</p>
              <button class="btn admin-btn" id="show-img" type="button">Visa bilder</button>
              <div class="user-images">
                <?php
                if (empty($images)) {
                  echo "<p>Du har inte laddat upp några bilder för denna användare!</p>";
                } else {
                ?>
                  <?php
                  foreach ($images as $image) {
                    $imagePath = '../uploads/' . $image['filename'];
                  ?>
                    <div>
                      <label>
                        <input type="radio" name="postImage" id="<?php echo htmlentities($image['filename']) ?>" value="<?php echo $image['id'] ?>" <?php if ($image['id'] == $postImage) { ?> checked="checked" <?php } ?>>
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
            <button class="btn admin-btn" type="submit" name="updatePost">Uppdatera inlägg</button>
          </form>
        <?php
        } else { ?>
          <h1>Redigera eller ta bort blogginlägg</h1>
          <p>Det finns inga inlägg att visa! Du kan skapa ett nytt inlägg genom att trycka på "Skapa nytt inlägg" i menyn.</p>
        <?php
        }

        ?>
      </div>
    </main>
    <?php require_once("includes/layout/footer.php"); ?>
  </div>
  <!-- CKEditor 4 CDN (texteditor) -->
  <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
  <!-- CKEDITOR -->
  <script>
    // Försök att hämta textarea
    const postContent = document.getElementById("postContent");
    if (postContent) {
      CKEDITOR.replace('postContent');
    }
  </script>
  <!-- Toggla bilder -->
  <script src="../js/show_images.js"></script>
</body>

</html>