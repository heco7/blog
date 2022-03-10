<?php

/** Skapar ett nytt blogginlägg som lagras i post-tabellen. Skaparen av inlägget identifieras utifrån sitt användarid. Returnerar true om query lyckas */
function createNewPost($conn, $title, $content, $userId, $imageId)
{
  $sql = "INSERT INTO post (title, content, userId, imageId) VALUES (?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);

  // Felhantering för prepare()
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }

  // Binda variabler till statement
  mysqli_stmt_bind_param($stmt, 'ssii', $title, $content, $userId, $imageId);

  // Exekvera SQL-fråga
  mysqli_stmt_execute($stmt);

  // Stäng prepared statement
  mysqli_stmt_close($stmt);

  return true;
}

/** Tar bort ett enstaka inlägg från post-tabellen baserat på dess unika ID. Funktionen bekräftar även att användaren är ägaren */
function deletePostById($conn, $id, $userId)
{
  $sql = "DELETE FROM post WHERE id=? AND userId=?";
  $stmt = mysqli_prepare($conn, $sql);

  // Felhantering för prepare()
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }

  // Binda variabler till statement. Typen 'i' används för heltal/integer
  mysqli_stmt_bind_param($stmt, 'ii', $id, $userId);

  // Exekvera SQL-fråga
  mysqli_stmt_execute($stmt);

  // Stäng prepared statement
  mysqli_stmt_close($stmt);

  return true;
}

/** Uppdaterar ett enstaka inlägg i post-tabellen. Funktionen bekräftar även att användaren är ägaren. Returnerar true om query lyckas */
function updatePost($conn, $title, $content, $imageId, $postId)
{
  $sql = "UPDATE post SET title=?, content=?, imageId=? WHERE id=?";

  $stmt = mysqli_prepare($conn, $sql);

  // Felhantering för prepare()
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }

  // Binda variabler till statement
  mysqli_stmt_bind_param($stmt, 'ssii', $title, $content, $imageId, $postId);

  // Exekvera SQL-fråga
  mysqli_stmt_execute($stmt) or die(db_error($conn));

  // Stäng prepared statement
  mysqli_stmt_close($stmt);

  return true;
}

/** Lägger till metadata för en uppladdad bild i databasen. Returnerar true om query lyckas */
function addImage($conn, $filename, $description, $userId)
{
  $sql = "INSERT INTO image (filename, description, userId) VALUES (?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);

  // Felhantering för prepare()
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }

  // Binda variabler till statement
  mysqli_stmt_bind_param($stmt, 'ssi', $filename, $description, $userId);

  // Exekvera SQL-fråga
  mysqli_stmt_execute($stmt);

  // Stäng prepared statement
  mysqli_stmt_close($stmt);

  return true;
}

/** Uppdatera beskrivning på uppladdad bild i databasen och bekräfta ägaren. Returnerar true om query lyckas */
function updateImage($conn, $id, $description, $userId)
{
  $sql = "UPDATE image SET description=? WHERE id=? AND userId=?";
  $stmt = mysqli_prepare($conn, $sql);

  // Felhantering för prepare()
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }

  // Binda variabler till statement
  mysqli_stmt_bind_param($stmt, 'sii', $description, $id, $userId);

  // Exekvera SQL-fråga
  mysqli_stmt_execute($stmt);

  // Stäng prepared statement
  mysqli_stmt_close($stmt);

  return true;
}

/** Tar bort en enstaka bild från image-tabellen baserat på dess unika ID. Funktionen bekräftar även att användaren är ägaren av bilden. Returnerar true om query lyckas */
function deleteImageById($conn, $id, $userId)
{
  $sql = "DELETE FROM image WHERE id=? AND userId=?";
  $stmt = mysqli_prepare($conn, $sql);

  // Felhantering för prepare()
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }

  // Binda variabler till statement. Typen 'i' används för heltal/integer
  mysqli_stmt_bind_param($stmt, 'ii', $id, $userId);

  // Exekvera SQL-fråga
  mysqli_stmt_execute($stmt);

  // Stäng prepared statement
  mysqli_stmt_close($stmt);
}

/** Returnerar en assoc array med alla bilder som tillhör en viss användare */
function getImagesByUserId($conn, $userId)
{
  $sql = "SELECT * FROM image WHERE userId=?";
  $stmt = mysqli_prepare($conn, $sql);

  // Felhantering för prepare()
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }

  // Binda variabler till statement
  mysqli_stmt_bind_param($stmt, 'i', $userId);

  // Exekvera SQL-fråga
  mysqli_stmt_execute($stmt);

  // Hämta rader
  $result = mysqli_stmt_get_result($stmt);

  // Stäng prepared statement
  mysqli_stmt_close($stmt);

  // Returnera som assoc array
  $result = mysqli_fetch_all($result, MYSQLI_ASSOC);

  return $result;
}

/** Returnerar assoc array av rad från image-tabellen utefter bildens ID och bekräftar att den tillhör en viss användare */
function getSingleImageById($conn, $imageId, $userId)
{
  $sql = "SELECT * FROM image WHERE id=? AND userId=?";
  $stmt = mysqli_prepare($conn, $sql);
  // Förbered SQL statement-mall för exekvering med felhantering
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }
  mysqli_stmt_bind_param($stmt, 'ii', $imageId, $userId);
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

/** Uppdatera profilbild i user-tabellen. Returnerar true om det lyckas */
function updateProfilePicture($conn, $filename, $userId)
{
  $sql = "UPDATE user SET image=? WHERE id=?";
  $stmt = mysqli_prepare($conn, $sql);

  // Felhantering för prepare()
  if (!$stmt) {
    die("prepare() misslyckades: " . htmlentities(db_error($conn)));
  }
  mysqli_stmt_bind_param($stmt, 'si', $filename, $userId);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  return true;
}
