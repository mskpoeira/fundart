<?php if (!defined('ABSPATH')) exit; ?><!doctype html>
<html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<a class="skip" href="#conteudo">Pular para o conteúdo principal</a>
<header class="site-header">
 <div class="container header-main">
  <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="FUNDART — início">
   <?php if (has_custom_logo()) { $logo_id=(int)get_theme_mod('custom_logo'); echo wp_get_attachment_image($logo_id,'medium',false,['alt'=>'Logotipo oficial da FUNDART']); } else { ?>
   <img src="<?php echo esc_url(get_template_directory_uri().'/assets/img/fundart-colorida.svg'); ?>" alt="FUNDART">
   <?php } ?>
   <span class="brand-text"><strong>FUNDART</strong><small>FUNDAÇÃO DE ARTE E CULTURA DE UBATUBA</small></span>
  </a>
  <div class="header-right">
   <div class="access" aria-label="Acessibilidade"><span>Acessibilidade</span><button data-font="up" type="button" aria-label="Aumentar fonte">A+</button><button data-font="down" type="button" aria-label="Diminuir fonte">A−</button><button data-contrast type="button" aria-pressed="false">◉ Alto contraste</button></div>
   <form class="search" role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get"><label class="screen-reader-text" for="fundart-search">Buscar no site</label><input id="fundart-search" type="search" name="s" placeholder="Buscar no site..."><button type="submit" aria-label="Buscar">⌕</button></form>
   <nav class="toplinks" aria-label="Serviços institucionais">
    <a href="<?php echo fundart_link('transparencia/'); ?>">Portal da Transparência</a>
    <a href="https://informabr.cgu.gov.br/">SIC</a>
    <a href="<?php echo fundart_link('ouvidoria/'); ?>">Ouvidoria</a>
    <a href="<?php echo fundart_link('contato/'); ?>">Contato</a>
    <a href="https://webmail.ubatuba.sp.gov.br/">Webmail</a>
   </nav>
  </div>
 </div>
 <div class="nav-wrap"><div class="container"><button class="menu-toggle" type="button" aria-controls="main-nav" aria-expanded="false">☰ Menu</button><nav class="main-nav" id="main-nav" aria-label="Menu principal"><?php wp_nav_menu(['theme_location'=>'principal','container'=>false,'fallback_cb'=>'fundart_fallback_menu']); ?></nav></div></div>
</header><main id="conteudo">