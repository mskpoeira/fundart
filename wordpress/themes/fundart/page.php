<?php get_header(); ?>
<?php while (have_posts()) : the_post();
  $slug = get_post_field('post_name', get_the_ID());
  $institutional = ['a-fundart','objetivos','equipe','institucional','contato','transparencia','conselhos','acessibilidade','privacidade','mapa-do-site'];
  $show_institutional = in_array($slug, $institutional, true);
?>
<header class="page-hero">
 <div class="container">
  <nav class="breadcrumbs" aria-label="Você está aqui"><a href="<?php echo esc_url(home_url('/')); ?>">Início</a><span aria-hidden="true">›</span><?php echo esc_html(get_the_title()); ?></nav>
  <h1><?php the_title(); ?></h1>
 </div>
</header>
<div class="container page-content <?php echo $show_institutional ? 'institutional-layout' : ''; ?>">
 <?php if ($show_institutional) : ?>
 <nav class="institutional-nav" aria-label="Seções institucionais da FUNDART">
  <h2>Institucional</h2>
  <ul>
   <?php foreach (['a-fundart'=>'A FundArt','objetivos'=>'Objetivos','equipe'=>'Equipe','conselhos'=>'Conselhos','transparencia'=>'Transparência','contato'=>'Contato'] as $linkslug => $label) :
    $linkpage = get_page_by_path($linkslug, OBJECT, 'page');
    if (!$linkpage || $linkpage->post_status !== 'publish') continue;
   ?>
   <li><a <?php echo $slug === $linkslug ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url(get_permalink($linkpage)); ?>"><?php echo esc_html($label); ?></a></li>
   <?php endforeach; ?>
  </ul>
 </nav>
 <?php endif; ?>
 <article id="post-<?php the_ID(); ?>" <?php post_class('institutional-article'); ?>>
  <?php the_content(); wp_link_pages(); ?>
 </article>
</div>
<?php endwhile; get_footer(); ?>
