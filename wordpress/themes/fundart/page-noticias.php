<?php
/**
 * Notícias: lista pública sempre ordenada pela data de publicação (mais recentes primeiro).
 * A paginação permanece determinística inclusive para notícias com a mesma data.
 */
if (!defined('ABSPATH')) exit;
get_header();
while (have_posts()) : the_post();
?>
<header class="page-hero"><div class="container">
  <nav class="breadcrumbs" aria-label="Você está aqui">
    <a href="<?php echo esc_url(home_url('/')); ?>">Início</a>
    <span aria-hidden="true">›</span> Notícias
  </nav>
  <h1><?php the_title(); ?></h1>
</div></header>
<div class="container page-content">
  <?php if (trim((string)get_the_content()) !== '') : ?>
  <div class="news-introduction"><?php the_content(); ?></div>
  <?php endif; ?>
  <?php
  $page=max(1,absint($_GET['pagina']??get_query_var('paged')?:1));
  $news=new WP_Query([
    'post_type'=>'post',
    'post_status'=>'publish',
    'posts_per_page'=>20,
    'paged'=>$page,
    'orderby'=>['date'=>'DESC','ID'=>'DESC'],
    'ignore_sticky_posts'=>true
  ]);
  ?>
  <section aria-label="Notícias em ordem da mais recente para a mais antiga">
    <div class="news-grid">
    <?php if ($news->have_posts()) : while ($news->have_posts()) : $news->the_post(); ?>
      <article class="news-card">
        <a href="<?php the_permalink(); ?>">
          <?php if (has_post_thumbnail()) the_post_thumbnail('medium_large',['loading'=>'lazy']); ?>
          <div class="body">
            <p><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('d/m/Y')); ?></time></p>
            <h2><?php the_title(); ?></h2>
            <p><?php echo esc_html(wp_trim_words(get_the_excerpt(),25)); ?></p>
          </div>
        </a>
      </article>
    <?php endwhile; else: ?>
      <p>Nenhuma notícia publicada até o momento.</p>
    <?php endif; wp_reset_postdata(); ?>
    </div>
    <?php if ($news->max_num_pages>1) : ?>
    <nav class="fundart-acervo-paginas" aria-label="Paginação das notícias">
      <?php if($page>1): ?><a href="<?php echo esc_url(add_query_arg('pagina',$page-1,get_permalink())); ?>">← Notícias mais recentes</a><?php endif; ?>
      <span>Página <?php echo esc_html((string)$page); ?> de <?php echo esc_html((string)$news->max_num_pages); ?></span>
      <?php if($page<$news->max_num_pages): ?><a href="<?php echo esc_url(add_query_arg('pagina',$page+1,get_permalink())); ?>">Notícias mais antigas →</a><?php endif; ?>
    </nav>
    <?php endif; ?>
  </section>
</div>
<?php endwhile; get_footer(); ?>
