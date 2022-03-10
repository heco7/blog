<?php
require_once("includes/admin_functions.php");
require_once("../includes/functions.php");
require_once("../includes/db.php");

session_start();

// Omdirigera om oautentiserad
if (!isset($_SESSION['id'])) {
  redirect("login.php");
}

// Anslut till databas
$conn = db_connect();

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
$tmpFilename = $uploadDir = $uploadFile = $fileInfo = $fileExtension = $width = $height = $successMessage = "";

// Array för felmeddelanden
$errors = array();

if (isset($_POST['submit'])) {
  // Hämta temporära filnamnet och sätt destination för profilbild
  $tmpFilename = $_FILES['fileUpload']['tmp_name'];
  $uploadDir = '../images/avatar/';

  // Hämta filändelse
  $fileExtension = strtolower(pathinfo(basename($_FILES['fileUpload']['name']), PATHINFO_EXTENSION));

  // Profilbildens namn i format användarnamn + filändelse (.jpg-format)
  $uploadFile = $_SESSION['username'] . ".jpg";

  // Ingen fil finns
  if (!file_exists($_FILES['fileUpload']['tmp_name'])) {
    array_push($errors, "Välj en fil att ladda upp");
  }
  // Ej giltig filändelse eller ej giltig bild (MYCKET VIKTIGT för att undvika skriptfiler och annat farligt/oönskat)
  else if (!in_array($fileExtension, $allowedExtensions) || !$fileInfo = getimagesize($tmpFilename)) {
    array_push($errors, "Filen måste vara i formatet JPG, JPEG eller PNG");
  }
  // Filen för stor (1 MB eller mer)
  else if ($_FILES['fileUpload']['size'] > 1000000) {
    array_push($errors, "Filen är för stor!");
  }
  // Bekräfta att bilden max har dimensionerna 400x400 pixlar
  else {
    // Hämta bildens bredd och höjd
    $width = $fileInfo[0];
    $height = $fileInfo[1];
    // Dimensioner utanför tillåtna ramarna
    if ($width > 400 || $height > 400) {
      array_push($errors, "Filen får max vara storlek 400x400!");
    }
  }

  // Inga fel inträffade, ladda upp
  if (empty($errors)) {
    // Flytta filen till permanent lagringsplats
    if (move_uploaded_file($tmpFilename, $uploadDir . $uploadFile)) {
      // Uppdatera metadata för profilbild i databasen
      if (updateProfilePicture($conn, $uploadFile, $_SESSION['id'])) {
        $successMessage = '<p class="success-message">Profilbild uppdaterad!';
      } else {
        array_push($errors, "Fel inträffade, försök igen");
      }
    }
    // Fel inträffade vid uppladdning
    else {
      $errorCode = $_FILES['fileUpload']['error'];
      array_push($errors, $uploadErrors[$errorCode]);
    }
  }
}

// Hämta profilbild
$profilePicture = getProfilePicture($conn, $_SESSION['id']);

db_disconnect($conn);
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
        <div class="change-pfp grid-2">
          <div class="description">
            <h1>Lägg till eller ändra din profilbild</h1>
            <p>På denna sidan kan du ändra din profilbild.</p>
            <p>Profilbilden måste vara av typ JPG, JPEG eller PNG och får inte vara större än 1 megabyte.</p>
            <p>Maximala dimensionerna för en profilbild är 400x400 pixlar.</p>
          </div>
          <div>
            <h2>Nuvarande profilbild</h2>
            <img class="current-pfp" src="<?php echo $profilePicture ?>" alt="profile picture">
          </div>
        </div>
        <form class="upload-form" enctype="multipart/form-data" method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>">
          <label for="fileUpload">Välj fil att ladda upp</label><br>
          <input type="file" name="fileUpload" id="fileUpload" accept="image/*">
          <button type="submit" name="submit" class="btn admin-btn">Ladda upp</button>
        </form>
      </div>
    </main>
    <?php require_once("includes/layout/footer.php"); ?>
  </div>
</body>

</html>