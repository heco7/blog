<?php
require_once("includes/admin_functions.php");
require_once("../includes/functions.php");
require_once("../includes/db.php");

session_start();

// Omdirigera om oautentiserad
if (!isset($_SESSION['id'])) {
  redirect("login.php");
}

// Deklarera variabler
$successMessage = "";
$isEditing = false;
$errors = array();

// Anslut till databas
$conn = db_connect();

// När användaren försöker ta bort en bild
if (isset($_POST['remove'])) {
  $imageId = $_POST['imageId'];

  // imageId måste vara ett giltigt ID
  if (validId($imageId)) {
    // Innan bilden tas bort, bekräfta att en bild med detta ID existerar och att den inloggade användaren är ägaren
    $image = getSingleImageById($conn, $imageId, $_SESSION['id']);
    // Om en rad returneras
    if ($image) {
      // Bekräfta att bilden inte redan används i ett inlägg, då blir det error i databasen!
      $imageId = db_escape($conn, $imageId);
      $sql = "SELECT * FROM post WHERE imageId='$imageId'";
      $imageInUse = db_select($conn, $sql);
      // Bilden används i ett inlägg!
      if ($imageInUse) {
        array_push($errors, "Bilden används redan i ett blogginlägg! Var vänlig att ta bort blogginlägget eller ändra inläggets huvudbild för att ta bort denna bild.");
      } else {
        // Ta bort raden från databasen
        deleteImageById($conn, $imageId, $_SESSION['id']);
        // Ta bort bilden från filsystemet
        unlink('../uploads/' . $image['filename']);
        $successMessage = "<p class='success-message'>Bilden har tagits bort</p>";
      }
    } else {
      array_push($errors, "Fel inträffade");
    }
  }
}
// När användaren försöker redigera en bild
else if (isset($_POST['edit'])) {
  $imageId = $_POST['imageId'];

  // imageId måste vara ett giltigt ID
  if (validId($imageId)) {
    // Innan bilden redigeras, bekräfta att en bild med detta ID existerar och att den inloggade användaren är ägaren
    $image = getSingleImageById($conn, $imageId, $_SESSION['id']);
    // Om en rad returneras
    if ($image) {
      $isEditing = true;
      $imagePath = '../uploads/' . $image['filename'];
      $_SESSION['imageId'] = $image['id'];
    } else {
      array_push($errors, "Fel vid redigering. Försök igen");
    }
  } else {
    array_push($errors, "Fel inträffade");
  }
}
// Uppdatera bildbeskrivning
else if (isset($_POST['update'])) {
  $isEditing = true;
  $imageId = $_SESSION['imageId'];
  $newDescription = trim($_POST['newDescription']);

  // Maximala bildbeskrivningslängd
  if (strlen($newDescription) > 30) {
    array_push($errors, "Bildbeskrivning får max innehålla 30 tecken");
  }
  // imageId måste vara ett giltigt ID
  if (validId($imageId)) {
    // Innan bildbeskrivning uppdateras, bekräfta att bilden med detta ID existerar och att den inloggade användaren är ägaren
    $image = getSingleImageById($conn, $imageId, $_SESSION['id']);
    // Om användaren är ägaren
    if ($image) {
      $imagePath = '../uploads/' . $image['filename'];
    } else {
      redirect("admin/edit_image.php?error=editimage");
    }
  } else {
    array_push($errors, "Ogiltigt ID");
  }

  // Inga fel inträffade, uppdatera bildbeskrivning
  if (empty($errors)) {
    $result = updateImage($conn, $imageId, $newDescription, $_SESSION['id']);
    if ($result) {
      redirect("admin/edit_image.php?success=editimage");
    }
  }
}

if (isset($_GET['success'])) {
  if ($_GET['success'] == "editimage") {
    $successMessage = "<p class='success-message'>Bilden har redigerats!</p>";
  }
}

// Hämta bilder
$images = getImagesByUserId($conn, $_SESSION['id']);

db_disconnect($conn);
?>

<?php require_once("includes/layout/head.php"); ?>
<title>Hantera bilder</title>
</head>

<body>
  <div class="admin-wrapper">
    <?php require_once("includes/layout/header.php"); ?>
    <?php require_once("includes/layout/sidebar.php"); ?>
    <main class="admin-main">
      <?php
      // Rendera eventuella meddelanden
      echo $successMessage;

      if (!empty($errors)) {
        renderErrors($errors);
      }
      ?>
      <?php
      // Bilder finns och ej i redigeringsläge
      if (!empty($images) && !$isEditing) {
      ?>
        <div class="images-container">
          <?php
          foreach ($images as $image) {
            $imagePath = '../uploads/' . $image['filename'];
          ?>
            <div class="gallery-image">
              <div class="gallery-image-card has-shadow">
                <a target="_blank" href="<?php echo $imagePath ?>">
                  <img src="<?php echo $imagePath ?>" alt="<?php echo htmlentities($image['description']) ?>">
                </a>
                <p class="gallery-image-description"><?php echo (htmlentities($image['description'])) ?></p>
              </div>
              <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post">
                <input type="hidden" name="imageId" value="<?php echo $image['id'] ?>">
                <div class="buttons flex-center">
                  <button class="btn admin-btn" name="edit">Redigera</button>
                  <button class="btn admin-btn" name="remove">Ta bort</button>
                </div>
              </form>
            </div>
          <?php
          }
          ?>
        </div>
      <?php
        // Redigerar bild
      } else if (!empty($images) && $isEditing) {
      ?>
        <div class="images-container">
          <div class="gallery-image">
            <div class="gallery-image-card has-shadow">
              <a target="_blank" href="<?php echo $imagePath ?>">
                <img src="<?php echo $imagePath ?>" alt="<?php echo htmlentities($image['description']) ?>">
              </a>
              <p class="gallery-image-description"><?php echo (htmlentities($image['description'])) ?></p>
            </div>
            <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post">
              <div class="form-group">
                <label for="newDescription">Ny beskrivning</label>
                <input class="form-control" type="text" name="newDescription" id="newDescription" autofocus>
              </div>
              <div class="buttons flex-center">
                <a href="edit_image.php">
                  <button class="btn admin-btn" name="remove" type="button">Gå tillbaka</button></a>
                <button class="btn admin-btn" name="update">Uppdatera</button>
              </div>
            </form>
          </div>
        </div>
      <?php
      } else { ?>
        <div class="page-item has-shadow">
          <h1>Hantera bloggbilder</h1>
          <p>Det finns inga bilder att visa! Du kan ladda upp bilder genom att trycka på "Ladda upp bloggbild" i menyn.</p>
        </div>
      <?php
      }
      ?>

    </main>
    <?php require_once("includes/layout/footer.php"); ?>
  </div>
</body>

</html>