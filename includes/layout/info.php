<!-- Del av bloggsida. Information om bloggen/bloggaren gärna med en bild. Info måste hämtas från databasen. -->
<div class="blog-info">
  <?php
  // Användare med query string ID existerar
  if (!empty($user)) { ?>
    <img class="profile-picture" src="<?php echo $profilePicturePath ?>" alt="" />
    <a class="blog-info-link normal-link" href="view.php?id=<?php echo $userId ?>">
      <?php echo ucwords(htmlentities($title)) ?>
    </a>
    <hr>
    <h2 class="text-center">Om bloggen</h2>
    <p><?php echo htmlentities($presentation) ?></p>
  <?php } else {
    echo "<p class='text-center'>Fel inträffade</p>";
  } ?>
</div>