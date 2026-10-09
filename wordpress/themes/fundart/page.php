<?php get_header(); ?>
<?php while(have_posts()):the_post(); ?>
<header class="page-hero"><div class="container"><h1><?php the_title(); ?></h1></div></header>
<div class="container page-content"><article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php the_content();wp_link_pages(); ?>
</article></div>
<?php endwhile;get_footer(); ?>