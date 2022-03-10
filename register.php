<?php
// Starta session
session_start();
// Databasfunktioner
require_once('includes/db.php');
// Övriga funktioner
require_once('includes/functions.php');

// Omdirigera om användaren är inloggad
if (isset($_SESSION['id'])) {
  redirect("index.php");
}

// Deklarera variabler
$username = $password = $passwordRepeat = $blogTitle = $blogPresentation =  $successMessage = "";

// Array för felmeddelanden
$errors = array();

// Omdirigera till registreringssidan utan formulär
if (isset($_POST['submit'])) {

  // Spara värden. Notera att det inte saneras här, programmet använder prepared statements senare. Whitespace strippas från början och slut (sker ej på lösenord, det hade varit förvirrande och försämrat lösenordspolicyn.) Användarnamnet görs till lowercase så att det inte är skiftlägeskänsligt.
  $username = strtolower(trim($_POST['username']));
  $password = $_POST['password'];
  $passwordRepeat = trim($_POST['passwordRepeat']);
  $blogTitle = trim($_POST['blogTitle']);
  $blogPresentation = trim($_POST['blogPresentation']);

  // Tomt fält finns
  if (emptyInputRegister($username, $password, $passwordRepeat, $blogTitle, $blogPresentation)) {
    array_push($errors, 'Varje fält måste vara ifyllt');
  }

  // Användarnamnet innehåller ogiltiga tecken
  if (invalidUsername($username)) {
    array_push($errors, 'Användarnamn får enbart innehålla bokstäver (a-z) och siffror (0-9) och måste vara mellan 3-15 tecken långt');
  }

  // Lösenorden stämmer ej överens
  if ($password !== $passwordRepeat) {
    array_push($errors, 'Lösenorden matchar ej');
  }

  // Lösenordet för svagt
  if (passwordPolicy($password)) {
    array_push($errors, 'Lösenordet måste innehålla minst 6 tecken');
  }

  // Bloggtitel för lång
  if (strlen($blogTitle) > 30) {
    array_push($errors, 'Bloggtitel får max innehålla 30 tecken');
  }

  // Bloggpresentation för lång
  if (strlen($blogPresentation) > 300) {
    array_push($errors, 'Bloggpresentation får max innehålla 300 tecken');
  }

  // Villkoren ej godkända
  if (!isset($_POST['terms'])) {
    array_push($errors, 'Du måste godkänna våra användarvillkor innan du skapar ett konto');
  }

  // Inga felmeddelanden, försök att skapa användare
  if (empty($errors)) {
    // Inputvalidering färdig, anslut till databas
    $conn = db_connect();

    // Användare finns redan, generera felmeddelande
    if (getUserByUsername($conn, $username)) {
      db_disconnect($conn);
      array_push($errors, 'Användare finns redan');
    }
    // Annars, skapa ny användare
    else {
      createUser($conn, $username, $password, $blogTitle, $blogPresentation);
      db_disconnect($conn);
      // Meddelande att det lyckades
      $successMessage = '<p class="success-message">Blogg skapad!</p>';
      $username = $password = $passwordRepeat = $blogTitle = $blogPresentation = "";
    }
  }
}


require_once('includes/layout/head.php');
?>

<title>SimpleBlog | Registrera</title>
</head>

<body>
  <?php require_once('includes/layout/header.php') ?>
  <!-- PAGE CONTENT START -->
  <div class="page-content">
    <div class="container">
      <main class="main-content flex-column has-shadow">
        <form class="user-form" action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post">
          <h1 class="text-center">Skapa en ny blogg</h1>
          <?php
          // Rendera eventuella meddelanden
          echo $successMessage;

          if (!empty($errors)) {
            renderErrors($errors);
          }
          ?>
          <div class="form-group">
            <label for="username">Användarnamn:</label>
            <input class="form-control" type="text" name="username" id="username" value="<?php echo htmlentities($username) ?>" title="Användarnamn, får enbart innehålla bokstäver (A-Z) och siffror" autofocus>
          </div>
          <div class="form-group">
            <label for="password">Lösenord:</label>
            <input class="form-control" type="password" name="password" id="password" title="Lösenord, måste innehålla minst 6 tecken">
          </div>
          <div class="form-group">
            <label for="passwordRepeat">Upprepa lösenord:</label>
            <input class="form-control" type="password" name="passwordRepeat" id="passwordRepeat" title="Repetera lösenordet från fältet ovan">
          </div>
          <div class="form-group">
            <label for="blogTitle">Bloggens titel:</label>
            <input class="form-control" type="text" name="blogTitle" id="blogTitle" value="<?php echo htmlentities($blogTitle) ?>" title="Bloggens titel, exempelvis 'Anders Matblogg'">
          </div>
          <div class="form-group">
            <label for="blogPresentation">Bloggens presentation:</label>
            <textarea class="form-control" name="blogPresentation" id="blogPresentation" title="Här skriver du en presentation till din blogg"><?php echo htmlentities($blogPresentation) ?></textarea>
          </div>
          <div class="form-group">
            <input type="checkbox" name="terms" id="terms" value="termsConsent">
            <label for="terms">Jag har läst och godkänner <a class="terms-link" target="_blank" href="terms.php">villkoren</a></label>
          </div>
          <button type="submit" class="btn" name="submit">Skapa blogg</button>
        </form>
      </main>
    </div>
  </div>
  <!-- PAGE CONTENT END -->

  <?php require_once('includes/layout/footer.php') ?>
</body>

</html>