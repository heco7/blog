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
$username = $password = "";

// Array för felmeddelanden
$errors = array();

if (isset($_POST['submit'])) {
  // Spara användarnamn och lösenord, användarnamnet blir av med whitespace i början och slut och görs till gemener för att inte vara skiftlägesskänslig
  $username = strtolower(trim($_POST['username']));
  $password = $_POST['password'];

  // Hanterar fallet då ett fält är tomt
  if (empty($username)) {
    array_push($errors, 'Användarnamn ej ifyllt');
  }

  if (empty($password)) {
    array_push($errors, 'Lösenord ej ifyllt');
  }

  // Båda fälten ifyllda, försök att logga in
  if (empty($errors)) {
    // Om kontouppgifterna stämmer överens med data i databasen så hämtas associativ array som representerar användaren
    $conn = db_connect();
    $userArray = loginAuthentication($conn, $username, $password);
    db_disconnect($conn);

    // Om raden ej är tom så är autentiseringen lyckad, man kan nu logga in
    if ($userArray) {
      // Starta session
      session_start();
      // Generera nytt sessions-ID för att förhindra Session Fixation-sårbarhet
      session_regenerate_id();
      // Deklarera sessionsvariabler och omdirigera
      $_SESSION['id'] = $userArray['id'];
      $_SESSION['username'] = $userArray['username'];
      redirect('admin/');
    }
    // Autentisering misslyckades
    else {
      array_push($errors, 'Fel användarnamn/lösenord');
    }
  }
}

require_once('includes/layout/head.php');
?>
<title>SimpleBlog | Logga in</title>
</head>

<body>
  <?php require_once('includes/layout/header.php') ?>
  <!-- PAGE CONTENT START -->
  <div class="page-content">
    <div class="container">
      <main class="main-content flex-column has-shadow">
        <form class="user-form" action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post">
          <h1 class="text-center">Logga in</h1>
          <?php
          // Rendera eventuella felmeddelanden
          if (!empty($errors)) {
            renderErrors($errors);
          }
          ?>
          <div class="form-group">
            <label for="username">Användarnamn:</label>
            <input class="form-control" type="text" name="username" id="username" value="<?php echo htmlentities($username) ?>" autofocus>
          </div>
          <div class="form-group">
            <label for="password">Lösenord:</label>
            <input class="form-control" type="password" name="password" id="password">
          </div>
          <button type="submit" class="btn" name="submit">Logga in</button>
        </form>
      </main>
    </div>
  </div>
  <!-- PAGE CONTENT END -->

  <?php require_once('includes/layout/footer.php') ?>
</body>

</html>