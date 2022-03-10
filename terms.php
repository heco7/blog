<?php
// Starta session
session_start();
// Databasfunktioner
require_once('includes/db.php');
// Övriga funktioner
require_once('includes/functions.php');

require_once('includes/layout/head.php'); ?>
<title>SimpleBlog | Villkor</title>
</head>

<body>
  <?php require_once('includes/layout/header.php') ?>
  <!-- PAGE CONTENT START -->
  <div class="page-content">
    <div class="container">
      <main class="main-content has-shadow flex-center">
        <div class="user-terms">
          <!-- Notera att detta är mer som en platshållare för tillfället -->
          <h1>Villkor</h1>
          <p>Genom att fortsätta att använda webbplatsen så godkänner du villkoren och cookiepolicyn som finns på denna sida.</p>
          <p>När du registrerar ett nytt konto så kommer eventuella personuppgifter som tillhör kontot att lagras i vår databas så länge som ditt konto existerar.</p>
          <p>Vi sparar inga personuppgifter om dig innan du själv väljer att registrera dig på sidan. Vi respekterar din integritet och kommer aldrig att ge bort dina uppgifter till en tredjepart.</p>
          <h2>Cookiepolicy</h2>
          <p>En cookie/kaka är en liten textfil som lagras i din webbläsare som kan användas för att identifiera dig och för att skräddersy webbsidan efter dina preferenser. Cookies som skapas på denna webbsida skickas till vår server varje gång som en förfråga görs.</p>
          <p>SimpleBlog använder enbart cookies för nödvändig funktionalitet som krävs för att webbsidan ska vara funktionell. Cookies är nödvändigt för att identifiera dig som unik användare så att du har möjlighet att ta del av den funktionalitet som webbplatsen erbjuder. Vi kommer aldrig att spåra din aktivitet eller att använda tredjepartscookies.</p>
        </div>
      </main>
    </div>
  </div>
  <!-- PAGE CONTENT END -->

  <?php require_once('includes/layout/footer.php') ?>
</body>

</html>