<?php get_header(); while(have_posts()):the_post(); ?>
<header class="page-hero"><div class="container"><h1><?php the_title(); ?></h1></div></header>
<div class="container page-content"><article <?php post_class(); ?>><?php if(has_post_thumbnail()) the_post_thumbnail('large'); ?><p><small>Publicado em <?php echo esc_html(get_the_date('d/m/Y')); ?></small></p><?php the_content(); ?></article></div>
<?php endwhile;get_footer(); ?>