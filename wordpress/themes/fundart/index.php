<?php get_header(); ?>
<header class="page-hero"><div class="container"><h1><?php if(is_home()) echo 'Notícias'; elseif(is_search()) echo 'Resultados para: '.esc_html(get_search_query()); else the_archive_title(); ?></h1></div></header>
<div class="container page-content"><div class="news-grid">
<?php if(have_posts()):while(have_posts()):the_post(); ?>
<article class="news-card"><a href="<?php the_permalink(); ?>"><?php if(has_post_thumbnail()) the_post_thumbnail('medium_large'); ?><div class="body"><p><?php echo esc_html(get_the_date('d/m/Y')); ?></p><h3><?php the_title(); ?></h3><p><?php echo esc_html(wp_trim_words(get_the_excerpt(),25)); ?></p></div></a></article>
<?php endwhile;else: ?><p>Nenhum conteúdo cadastrado ainda. Consulte o <a href="<?php echo fundart_original(); ?>">acervo atual da FUNDART</a>.</p><?php endif; ?>
</div><?php the_posts_pagination(); ?></div>
<?php get_footer(); ?>