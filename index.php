<?php get_header(); ?>

<main>
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
        <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <div class="entry-meta"><?php echo get_the_date(); ?></div>
        <div class="post-content">
          <?php the_excerpt(); ?>
        </div>
      </article>
    <?php endwhile;
  else: ?>
    <p>No posts found.</p>
  <?php endif; ?>
</main>


<?php get_footer(); ?>