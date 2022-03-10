<?php
require_once("includes/admin_functions.php");
require_once("../includes/functions.php");
require_once("../includes/db.php");

session_start();

// Omdirigera om oautentiserad
if (!isset($_SESSION['id'])) {
  redirect("login.php");
}

?>

<?php require_once("includes/layout/head.php"); ?>
<title>Admin Dashboard</title>
</head>

<body>
  <div class="admin-wrapper">
    <?php require_once("includes/layout/header.php"); ?>
    <?php require_once("includes/layout/sidebar.php"); ?>
    <main class="admin-main">
      <div class="page-item has-shadow">
        <h1>Administrationspanel</h1>
        <p>Här kan du skapa, ändra och ta bort blogginlägg som tillhör din blogg.</p>
        <p>Du kan även ladda upp bilder som senare kan inkluderas i dina blogginlägg.</p>
        <p>Om du önskar kan du även ändra din profilbild.</p>
      </div>
    </main>
    <?php require_once("includes/layout/footer.php"); ?>
  </div>
</body>

</html>