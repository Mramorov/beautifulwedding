<?php get_header(); ?>


<main>
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <div class="post-content">
                    <?php the_content(); ?>
                </div>
            </article>
    <?php endwhile;
    endif; ?>
</main>


<?php get_footer(); ?>