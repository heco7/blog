<?php

// Inloggningsuppgifter för databasen
define('DB_SERVER', 'NICE_TRY');
define('DB_USER', 'NICE_TRY');
define('DB_PASS', 'NICE_TRY');
define('DB_NAME', 'NICE_TRY');

/** Kopplar upp oss mot databasen */
function db_connect()
{
  // Kopplar upp mot databasen
  $connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

  // Kollar om något gick fel, i så fall skrivs orsaken ut och skriptet avslutas
  if (mysqli_connect_errno()) {
    $msg = "Databasuppkopplingen misslyckades: ";
    $msg .= mysqli_connect_error();
    $msg .= " (" . mysqli_connect_errno() . ")";
    exit($msg);
  }

  // Sätter teckenkodning för kommunikation med databas till UTF-8
  mysqli_set_charset($connection, 'utf8');
  return $connection;
}

/** Koppla ner från databasen */

function db_disconnect($connection)
{
  if (isset($connection))
    mysqli_close($connection);
}

/** Utför en databasfråga (query) */
function db_query($connection, $query)
{
  $result = mysqli_query($connection, $query);
  if (!$result)
    echo '<br>Fel i frågan: <strong>\'' . $query . '\'</strong>:<br>' . db_error($connection) . '<br>';
  return $result;
}

/**
 * Utför en SELECT-fråga (query)
 * Returnerar resultatet som en array där nyckel är kolumnens namn
 *
 * @param $connection
 * @param $query
 * @return array
 */
function db_select($connection, $query)
{
  $rows = array();
  $result = db_query($connection, $query);
  if ($result) {
    // Hämta rad för rad ur resultatet och lägg in i $row
    while ($row = mysqli_fetch_assoc($result)) {
      $rows[] = $row;
    }
  }
  return $rows;
}

/**
 * Denna funktion anropar mysqli_real_escape_string
 * Används på all dynamisk data som ska skickas till databasen
 * @param $connection
 * @param $str
 * @return string
 */
function db_escape($connection, $str)
{
  return mysqli_real_escape_string($connection, $str);
}

/** Hämtar det senaste felet som upstått i databasen */
function db_error($connection)
{
  if (isset($connection))
    return mysqli_error($connection);
  return mysqli_connect_error();
}

/**
 * OBS! Kan ta bort alla tabeller ut databasen om så önskas
 *
 * Importerar databastabeller och innehåll i databasen från en .sql-fil
 * Använd MyPhpAdmin för att exportera din lokala databas till en .sql-fil
 *
 * @param $db
 * @param $filename
 * @param $dropOldTables - skicka in TRUE om alla tabeller som finns ska tas bort
 */
function db_import($db, $filename, $dropOldTables = FALSE)
{
  // Om $dropOldTables är TRUE så ska vi ta bort alla gamla tabeller
  if ($dropOldTables) {
    // Börjar med att hämta eventuella tabeller som finns i databasen
    $query = 'SHOW TABLES';
    $result = db_query($db, $query);
    // Om några tabeller hämtats
    if ($result) {
      // Hämta rad för rad ur resultatet
      while ($row = mysqli_fetch_row($result)) {
        $query = 'DROP TABLE ' . $row[0];
        //echo $query . '<br>';
        if (db_query($db, $query))
          echo 'Tabellen <strong>' . $row[0] . '</strong> togs bort<br>';
      }
    }
  }
  $query = '';
  // Läs in filens innehåll
  $lines = file($filename);

  // Hantera en rad i taget
  foreach ($lines as $line) {
    // Gör inget med kommentarer eller tomma rader (gå till nästa rad)
    if (substr($line, 0, 2) == '--' || $line == '')
      continue;

    // Varje rad läggs till i frågan (query)
    $query .= $line;

    // Slutet på frågan är hittad om ett semikolon hittades i slutet av raden
    if (substr(trim($line), -1, 1) == ';') {
      //echo $query . '<br>';
      // Kör frågan mot databasen
      if (!db_query($db, $query))
        echo '<br>Fel i frågan: <strong>\'' . $query . '\'</strong>:<br>' . db_error($db) . '<br>';

      // Töm $query så vi kan starta med nästa fråga
      $query = '';
    }
  }
  echo 'Importeringen är klar!<br>';
}
