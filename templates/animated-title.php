<?php
/**
 * Template part: Animated title
 * Displays page/post title with character-by-character animation
 */
$post_id = get_queried_object_id();
$title_text = get_the_title($post_id);
$words = explode(' ', $title_text);
$char_index = 0;
?>
<h1 class="entry-title svadba-animated-title">
  <?php foreach ($words as $word_idx => $word): ?>
    <?php if ($word_idx > 0): ?><span class="word-space"> </span><?php endif; ?>
    <span class="word">
      <?php
      $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
      foreach ($chars as $ch):
      ?>
        <span class="char" style="--i: <?php echo (int)$char_index; ?>;">
          <?php echo esc_html($ch); ?>
        </span>
      <?php
        $char_index++;
      endforeach;
      ?>
    </span>
  <?php endforeach; ?>
</h1>
