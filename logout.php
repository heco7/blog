<?php
require_once('includes/functions.php');

// Denna filen loggar ut en autentiserad användare från webbsidan

session_start();

if (isset($_SESSION['id'])) {
  logout();
} else {
  redirect('index.php');
}
