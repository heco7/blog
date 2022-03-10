<!-- Del av bloggsida. En klickbar lista med alla bloggens inlägg. Info måste hämtas från databasen. -->
<div class="blog-menu">
  <?php if (!empty($posts)) {
  ?>
    <h2 class="text-center">Välj ett blogginlägg</h2>
    <?php
    foreach ($posts as $post) {
      $postId = $post['id'];
      $postTitle = $post['title'];
      $uploaded = $post['created'];
    ?>
      <a class="blog-menu-link" href="view.php?id=<?php echo $userId ?>&post=<?php echo $postId ?>">
        <div>
          <div><?php echo htmlentities($postTitle); ?></div>
          <div><?php
                // Visa bara datum, ej exakt tid
                echo substr($uploaded, 0, 10);
                ?></div>
        </div>
      </a>
  <?php
    }
  } else {
    echo "<p class='text-center no-margin'>Inga inlägg hittades</p>";
  } ?>
</div>