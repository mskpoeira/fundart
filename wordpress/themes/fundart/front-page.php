<?php get_header(); ?>
<?php
$hero_image=trim((string)get_theme_mod('fundart_hero_image',''));
$hero_style=$hero_image ? ' style="background-image:linear-gradient(90deg,#061d38df,#061d382a),url('.esc_url($hero_image).')"' : '';
$features=[
 ['Agenda Cultural','📅','agenda/'],
 ['Oficinas Culturais','🎭','oficinas/'],
 ['Editais e Chamamentos','▤','editais/'],
 ['Projetos e Ações','♟','projetos-acoes/'],
 ['Conselhos Municipais','▥','conselhos/'],
 ['Ouvidoria Setorial da FUNDART','●','ouvidoria/'],
 ['Transparência e Acesso à Informação','⌕','transparencia/']
];
?>
<section class="hero"<?php echo $hero_style; ?>>
 <div class="container hero-inner">
  <span class="eyebrow">DESTAQUE</span>
  <h1><?php echo esc_html(get_theme_mod('fundart_hero_title','Ubatuba em Cena 2026')); ?></h1>
  <p><?php echo esc_html(get_theme_mod('fundart_hero_subtitle','Arte, cultura e diversidade em toda a cidade.')); ?></p>
  <a class="hero-cta" href="<?php echo esc_url(get_theme_mod('fundart_hero_url',home_url('/agenda/'))); ?>">Saiba mais →</a>
 </div>
</section>
<section class="container" aria-label="Acesso rápido"><div class="quick-grid">
<?php foreach ($features as $feature): ?>
 <a class="quick-card" href="<?php echo fundart_link($feature[2]); ?>"><span class="icon" aria-hidden="true"><?php echo esc_html($feature[1]); ?></span><strong><?php echo esc_html($feature[0]); ?></strong></a>
<?php endforeach; ?>
</div></section>
<section class="section"><div class="container panel"><div class="section-head"><h2>▦ Agenda Cultural</h2><a href="<?php echo fundart_link('agenda/'); ?>">Ver toda a agenda →</a></div>
 <div class="event-grid">
 <?php $events=fundart_home_items('fundart_evento',4); if($events->have_posts()):
 while($events->have_posts()):$events->the_post(); ?>
  <article class="event-card"><a href="<?php the_permalink(); ?>">
  <?php if(has_post_thumbnail()) the_post_thumbnail('medium_large',['loading'=>'lazy']); else echo '<div class="placeholder">FUNDART • EVENTO</div>'; ?>
  <div class="body"><h3><?php the_title(); ?></h3><p><?php echo esc_html(wp_trim_words(get_the_excerpt(),16)); ?></p></div></a></article>
 <?php endwhile;wp_reset_postdata();else: ?>
  <div class="event-card"><div class="body"><h3>Programação cultural</h3><p>Acompanhe os eventos na agenda oficial.</p><a href="<?php echo fundart_original('agenda/'); ?>">Consultar a agenda atual →</a></div></div>
 <?php endif; ?>
 </div>
</div></section>
<section class="section"><div class="container two-col">
 <div class="panel"><div class="section-head"><h2>◀ Notícias</h2><a href="<?php echo fundart_link('noticias/'); ?>">Ver todas →</a></div>
 <div class="news-grid">
 <?php $news=fundart_home_items('post',3);if($news->have_posts()):while($news->have_posts()):$news->the_post(); ?>
 <article class="news-card"><a href="<?php the_permalink(); ?>"><?php if(has_post_thumbnail()) the_post_thumbnail('medium_large',['loading'=>'lazy']);else echo '<div class="placeholder">FUNDART</div>'; ?><div class="body"><p><?php echo esc_html(get_the_date('d/m/Y')); ?></p><h3><?php the_title(); ?></h3><p><?php echo esc_html(wp_trim_words(get_the_excerpt(),18)); ?></p></div></a></article>
 <?php endwhile;wp_reset_postdata();else: ?>
 <article class="news-card"><div class="body"><h3>Notícias institucionais</h3><p>O acervo histórico permanece disponível no site atual durante a migração.</p><a href="<?php echo fundart_original('noticias/'); ?>">Ver notícias →</a></div></article>
 <?php endif; ?>
 </div></div>
 <aside class="panel"><div class="section-head"><h2>🔗 Acesso rápido</h2></div><div class="access-grid">
 <a href="<?php echo fundart_link('pnab/'); ?>">PNAB</a>
 <a href="<?php echo fundart_link('lei-paulo-gustavo/'); ?>">Lei Paulo Gustavo</a>
 <a href="<?php echo fundart_link('equipamentos-culturais/'); ?>">Equipamentos Culturais</a>
 <a href="<?php echo fundart_link('patrimonio-cultural/'); ?>">Patrimônio Cultural</a>
 <a href="<?php echo fundart_link('historia-de-ubatuba/'); ?>">História de Ubatuba</a>
 <a href="<?php echo fundart_link('cultura-caicara/'); ?>">Cultura Caiçara</a>
 <a href="<?php echo fundart_link('cultura-indigena/'); ?>">Cultura Indígena</a>
 <a href="<?php echo fundart_link('cultura-quilombola/'); ?>">Cultura Quilombola</a>
 </div></aside>
</div></section>
<?php get_footer(); ?>