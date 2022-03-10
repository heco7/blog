<!-- Del av en bloggsida. Innehåller det valda inlägget eller det senaste inlägget om inget inlägg är valt. Info hämtas från databasen. -->
<div class="blog-content">
  <?php
  // Visa senaste inlägget om inget inlägg är valt och inlägg finns
  if (!empty($posts) && !isset($_GET['post'])) {
    $mostRecentPost = $posts[count($posts) - 1];
    renderPost($conn, $mostRecentPost);
  }
  // Visa valt inlägg
  else if (!empty($posts) && !empty($currentPost)) {
    renderPost($conn, $currentPost);
  }
  // Vid fel
  else {
    echo "<p class='no-margin'>Det finns inget inlägg att visa!</p>";
  } ?>
</div>