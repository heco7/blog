<?php
require_once("includes/admin_functions.php");
require_once("../includes/functions.php");
require_once("../includes/db.php");

session_start();

// Omdirigera om oautentiserad
if (!isset($_SESSION['id'])) {
  redirect("login.php");
}

// Källor:
// https://phppot.com/php/php-image-upload-with-size-type-dimension-validation/
// https://www.w3schools.com/php/php_file_upload.asp
// https://stackoverflow.com/questions/38509334/full-secure-image-upload-script

// Möjliga felmeddelanden vid uppladdning
$uploadErrors = array(
  // http://www.php.net/manual/en/features.file-upload.errors.php
  UPLOAD_ERR_OK          => "Inga fel.",
  UPLOAD_ERR_INI_SIZE    => "Filen är större än den storlek som anges i php.ini (upload_max_filesize).",
  UPLOAD_ERR_FORM_SIZE   => "Filen är större än den största filstorlek som angets i formuläret (MAX_FILE_SIZE).",
  UPLOAD_ERR_PARTIAL     => "Filen blev delvis uppladdad.",
  UPLOAD_ERR_NO_FILE     => "Ingen fil är vald.",
  UPLOAD_ERR_NO_TMP_DIR  => "Ingen temporär katalog finns på webbservern.",
  UPLOAD_ERR_CANT_WRITE  => "Kan inte skriva till disk.",
  UPLOAD_ERR_EXTENSION   => "Filuppladdningen är stoppad av ett tillägg (extension)."
);

// Tillåtna filändelser för uppladdade bilder (whitelist)
$allowedExtensions = array(
  'png',
  'jpg',
  'jpeg'
);
// Deklarera variabler
$tmpFilename = $uploadDir = $uploadFile = $description = $successMessage = "";
$errors = array();

// Försök att ladda upp fil
if (isset($_POST['submit'])) {
  // Hämta filens tmp-namn och ange katalog i filsystemet där bilden ska sparas
  $tmpFilename = $_FILES['fileUpload']['tmp_name'];
  $uploadDir = '../uploads/';

  // Skapa unikt filnamn för lagring på filsystemet. Filen ska även lagras som en JPG-fil
  $uploadFile = time() . ".jpg";
  $description = trim($_POST['description']);

  // Hämta filändelse
  $fileExtension = strtolower(pathinfo(basename($_FILES['fileUpload']['name']), PATHINFO_EXTENSION));

  // Ingen fil finns
  if (!file_exists($_FILES['fileUpload']['tmp_name'])) {
    array_push($errors, "Du har inte valt en bild att ladda upp!");
  }
  // Ingen/för lång bildbeskrivning
  else if (empty($description)) {
    array_push($errors, "Bilden saknar beskrivning");
  } else if (strlen($description) > 30) {
    array_push($errors, "Bildbeskrivningen får max innehålla 30 tecken");
  }
  // Ej giltig filändelse eller ej giltig bild (MYCKET VIKTIGT för att undvika skriptfiler och annat farligt/oönskat)
  else if (!in_array($fileExtension, $allowedExtensions) || !getimagesize($tmpFilename)) {
    array_push($errors, "Filen måste vara i formatet JPG, JPEG eller PNG");
  }
  // Filen finns redan
  else if (file_exists($uploadDir . $uploadFile)) {
    array_push($errors, "Filen finns redan!");
  }
  // Filen för stor (1 MB eller mer)
  else if ($_FILES['fileUpload']['size'] > 1000000) {
    array_push($errors, "Filen är för stor!");
  }

  // Inga fel inträffade, ladda upp
  if (empty($errors)) {
    // Flytta filen till den permanenta lagringsplatsen uploads
    if (move_uploaded_file($tmpFilename, $uploadDir . $uploadFile)) {
      // Lägg bildens metadata i databasen
      $conn = db_connect();
      if (addImage($conn, $uploadFile, $description, $_SESSION['id'])) {
        $successMessage = '<p class="success-message">Filen har laddats upp!</p>';
      } else {
        array_push($errors, "Fel inträffade, försök igen");
      }
      db_disconnect($conn);
    }
    // Fel inträffade vid uppladdning
    else {
      $errorCode = $_FILES['fileUpload']['error'];
      array_push($errors, $uploadErrors[$errorCode]);
    }
  }
}
?>

<?php require_once("includes/layout/head.php"); ?>
<title>Ladda upp bild</title>
</head>

<body>
  <div class="admin-wrapper">
    <?php require_once("includes/layout/header.php"); ?>
    <?php require_once("includes/layout/sidebar.php"); ?>
    <main class="admin-main">
      <div class="page-item has-shadow">
        <?php
        // Rendera eventuella meddelanden
        echo $successMessage;

        if (!empty($errors)) {
          renderErrors($errors);
        }

        ?>
        <h1>Ladda upp en bild</h1>
        <p>På den här sidan kan du ladda upp bilder som sedan kan användas för dina blogginlägg.</p>
        <p>Filen måste vara av typ JPG, JPEG eller PNG och får inte vara större än 1 megabyte.</p>
        <form class="upload-form" enctype="multipart/form-data" method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>">
          <div class="form-group">
            <label for="description">Bildens beskrivning</label>
            <input class="form-control" type="text" id="description" name="description">
          </div>
          <label for="fileUpload">Välj fil att ladda upp</label>
          <div class="file-upload-buttons">
            <input type="file" name="fileUpload" id="fileUpload" accept="image/*">
            <button type="submit" name="submit" class="btn admin-btn">Ladda upp</button>
          </div>
        </form>
      </div>
    </main>
    <?php require_once("includes/layout/footer.php"); ?>
  </div>
</body>

</html>