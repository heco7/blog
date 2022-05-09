<?php

// HTTP-rotväg för webbsidan
define('ROOT_HTTP', 'http://www.example.com/');

/** Omdirigerar till en viss URL. Utgår från sidans egen rotkatalog, ange t.ex parametern 'index.php' för att omdirigera till startsidan
 */
function redirect($path)
{
  header('Location: ' . ROOT_HTTP . $path);
  exit();
}

/** Undersök om en sträng (t.ex. query parameter) innehåller ett giltigt ID */
function validId($str)
{
  $result = preg_match('/^[1-9]{1}[0-9]*$/', $str);
  return $result;
}

/** Funktion som renderar alla felmeddelanden som finns i en array om den ej är tom. 
 * Varje felmeddelande får sin egen paragraph-tagg med klassen error-message. Varje felmeddelande escapeas även med htmlentities()
 */
function renderErrors($errors)
{
  if (is_array($errors) && !empty($errors)) {
    foreach ($errors as $error) {
      echo '<p class="error-message">' . htmlentities($error) . '</p>';
    }
  }
}

// ===== FUNKTIONER FÖR REGISTRERING, INLOGGNING OCH UTLOGGNING =====

/** Returnerar true om något av fälten vid registrering är tomma */
function emptyInputRegister($username, $password, $passwordRepeat, $blogTitle, $blogPresentation)
{
  return empty($username) || empty($password) || empty($passwordRepeat) || empty($blogTitle) || empty($blogPresentation);
}

/** Returnerar true om användarnamnet är ogiltigt (dvs innehåller annat än bokstäver och siffror och/eller är för kort/långt) */
function invalidUsername($username)
{
  return !preg_match("/^[a-zA-Z0-9]{3,15}$/", $username);
}

/**  Lösenordspolicy, returnerar true om lösenordet innehåller färre än 6 tecken. Uppenbarligen en väldig svag policy och bara lämpligt för ett labbprojekt */
function passwordPolicy($password)
{
  return strlen($password) < 6;
}

/** Returnerar användarraden i user-tabellen som associativ array om användaren finns, annars false */
function getUserByUsername($conn, $username)
{
  // Mall för SQL query, ? är platshållare för prepared statement
  $sql = "SELECT * FROM user WHERE username=?";

  // Förbereder mallen
  $stmt = mysqli_prepare($conn, $sql);

  // Felhantering för prepare()
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }

  // Binda variabler till statement. Typen 's' innebär en enstaka sträng
  mysqli_stmt_bind_param($stmt, 's', $username);

  // Exekvera SQL-fråga
  mysqli_stmt_execute($stmt);

  // Hämta data från prepared statement
  $result = mysqli_stmt_get_result($stmt);

  // Stäng prepared statement
  mysqli_stmt_close($stmt);

  // Spara rad som associativ array och returnera om raden finns
  $row = mysqli_fetch_assoc($result);
  if ($row) {
    return $row;
  } else {
    return false;
  }
}

/** Skapa en ny användare i user-tabellen */
function createUser($conn, $username, $password, $blogTitle, $blogPresentation)
{
  // Mall för SQL query, ? är platshållare för prepared statement
  $sql = "INSERT INTO user (username, password, title, presentation) VALUES (?, ?, ?, ?)";

  // Förbereder mallen
  $stmt = mysqli_prepare($conn, $sql);

  // Felhantering för prepare()
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }

  // Hasha och salta lösenordet med standard-algoritmen (just nu bcrypt) (VIKTIGT!)
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  // Binda variabler till statement. I detta fallet fyra stränger, så typen är ssss
  mysqli_stmt_bind_param($stmt, 'ssss', $username, $hashedPassword, $blogTitle, $blogPresentation);

  // Exekvera och stäng statement
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
}

/** Returnerar användarens user-rad som associativ array om användaren finns och lösenordet stämmer överens, annars false */
function loginAuthentication($conn, $username, $password)
{
  $user = getUserByUsername($conn, $username);
  if ($user) {
    $hashedPassword = $user['password'];

    // jämför inmatade lösenordets hash med hashen i databasen som tillhör denna användare
    if (password_verify($password, $hashedPassword)) {
      return $user;
    }
  }

  return false;
}

/** Städar bort session-variabler, förstör all data tillhörande sessionen och omdirigerar användaren till startsidan (index.php)
 */
function logout()
{
  session_unset();
  session_destroy();
  redirect('index.php?success=logout');
}


// ===== FUNKTIONER FÖR ATT HÄMTA BLOGGDATA =====

/** Hämtar alla inlägg från databasen */
function getPosts($conn)
{
  $sql = "SELECT * FROM post";
  $result = db_query($conn, $sql);

  // Hämta alla inlägg och spara i associativ array
  $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

  return $posts;
}

/** Returnerar en associativ array med användardata från user-tabellen i den rad som innehåller det angivna användar-ID:t
 */
function getUserById($conn, $id)
{
  $sql = "SELECT * FROM user WHERE id=?";
  $stmt = mysqli_prepare($conn, $sql);

  // Förbered SQL statement-mall för exekvering med felhantering
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }

  mysqli_stmt_bind_param($stmt, 'i', $id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  mysqli_stmt_close($stmt);

  // Om användaren ej finns
  if (!$result) {
    die("Användare med ID " . htmlentities($id) . "finns ej!");
  }

  // Spara i associativ array
  $result = mysqli_fetch_assoc($result);
  return $result;
}

/** Returnerar de 10 nyaste inläggen och dess skapare i en associativ array */
function getRecentPosts($conn)
{
  $sql = "SELECT user.username, user.id as userId, post.title, post.content, post.created, post.id as postId, post.imageId as imageId FROM post JOIN user ON post.userId=user.id ORDER BY post.created DESC LIMIT 10";
  $result = db_select($conn, $sql);
  return $result;
}

/** Returnerar en associativ array med alla bloggarna (id, användarnamn och bloggtitel) sorterat så att nyaste bloggare kommer först */
function getRecentBlogs($conn)
{
  $sql = "SELECT id, username, title FROM user ORDER BY created DESC";
  $result = db_select($conn, $sql);
  return $result;
}

/** Hämta alla inlägg som tillhör ett visst användarID */
function getPostsByUserId($conn, $id)
{
  $sql = "SELECT * FROM post WHERE userId=?";
  $stmt = mysqli_prepare($conn, $sql);
  // Förbered SQL statement-mall för exekvering med felhantering
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }
  mysqli_stmt_bind_param($stmt, 'i', $id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  mysqli_stmt_close($stmt);

  // Inget resultat
  if (!$result) {
    die("Dataåtkomst misslyckades");
  }

  // Spara i associativ array
  $result = mysqli_fetch_all($result, MYSQLI_ASSOC);
  return $result;
}

/** Hämta ett enstaka inlägg (baserat på ett inläggsID) som tillhör ett visst användarID. Returnerar en assoc array */
function getSinglePostFromUser($conn, $postId, $userId)
{
  $sql = "SELECT * FROM post WHERE id=? AND userId=?";
  $stmt = mysqli_prepare($conn, $sql);
  // Förbered SQL statement-mall för exekvering med felhantering
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }
  mysqli_stmt_bind_param($stmt, 'ii', $postId, $userId);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  mysqli_stmt_close($stmt);

  // Inget resultat
  if (!$result) {
    die("Dataåtkomst misslyckades");
  }
  // Spara i associativ array
  $result = mysqli_fetch_assoc($result);
  return $result;
}

/** Hämtar bildadressen till en användares profilbild utifrån dess användarID */
function getProfilePicture($conn, $id)
{
  // Sökväg till profilbildskatalog
  $path = ROOT_HTTP . 'images/avatar/';
  // Hämta användardata
  $user = getUserById($conn, $id);
  // Spara profilbild
  $profilePicture = $user['image'];
  return $path . $profilePicture;
}

/** Hämtar bildmetadata för bild som tillhör ett specifikt inlägg */
function getPostImage($conn, $postId)
{
  $sql = "SELECT image.filename, image.description FROM image JOIN post ON post.imageId=image.id WHERE post.id=?";
  $stmt = mysqli_prepare($conn, $sql);
  // Förbered SQL statement-mall för exekvering med felhantering
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }
  mysqli_stmt_bind_param($stmt, 'i', $postId);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  mysqli_stmt_close($stmt);

  // Inget resultat
  if (!$result) {
    die("Dataåtkomst misslyckades");
  }
  // Spara i associativ array
  $result = mysqli_fetch_assoc($result);
  return $result;
}

/** Hämtar ett enstaka inlägg utefter inläggets ID (använd efter du har hämtat alla inlägg från användaren för att undvika onödiga SQL-frågor) */
function findPostById($posts, $id)
{
  $result = array();
  foreach ($posts as $post) {
    if ($post['id'] == $id) {
      $result = $post;
    }
  }
  return $result;
}

/** Rendera inlägg på bloggsida. Förutsätter att parametern är en rad från post-tabellen i formatet assoc-array */
function renderPost($conn, $post)
{
  // Försök att hämta tillhörande bild
  $postImage = getPostImage($conn, $post['id']);

  // HTML-utskrift
?>
  <h1 class="current-post-title"><?php echo htmlentities($post['title']) ?></h1>
  <?php if ($postImage) { ?>
    <img src="<?php echo "uploads/" . $postImage['filename'] ?>" alt="<?php echo htmlentities($postImage['description']) ?>" class="current-post-main-image">
<?php
  }
  echo $post['content'];
  echo "<i class='current-post-date'>Inlägget skapat " . $post['created'] . "</i>";
}
