<?php
declare(strict_types=1);
if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', function (): void {
  load_theme_textdomain('fundart', get_template_directory() . '/languages');
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('custom-logo', ['height'=>100,'width'=>250,'flex-width'=>true,'flex-height'=>true]);
  add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
  register_nav_menus(['principal'=>'Menu principal','rodape'=>'Links do rodapé']);
});

add_action('wp_enqueue_scripts', function (): void {
  wp_enqueue_style('fundart-font','https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap',[],null);
  wp_enqueue_style('fundart-main',get_stylesheet_uri(),[],wp_get_theme()->get('Version'));
  wp_enqueue_script('fundart-a11y',get_template_directory_uri().'/assets/js/theme.js',[],wp_get_theme()->get('Version'),true);
});

add_action('init', function (): void {
  $types=[
    'fundart_evento'=>['Eventos culturais','Evento cultural','Eventos','calendar-alt'],
    'fundart_oficina'=>['Oficinas','Oficina','Oficinas','art'],
    'fundart_edital'=>['Editais','Edital','Editais','media-document'],
    'fundart_conselho'=>['Conselhos','Conselho','Conselhos','groups'],
  ];
  foreach ($types as $type=>$info){
    register_post_type($type,[
      'labels'=>['name'=>$info[0],'singular_name'=>$info[1],'add_new_item'=>'Adicionar '.$info[1]],
      'public'=>true,'show_in_rest'=>true,'has_archive'=>true,
      'rewrite'=>['slug'=>$info[2] === 'Eventos' ? 'eventos' : strtolower(remove_accents($info[2]))],
      'supports'=>['title','editor','thumbnail','excerpt','revisions','custom-fields'],
      'menu_icon'=>'dashicons-'.$info[3],
    ]);
  }
  register_taxonomy('fundart_tipo_edital',['fundart_edital'],[
    'labels'=>['name'=>'Categorias dos editais'],'hierarchical'=>true,'show_in_rest'=>true,
    'show_admin_column'=>true,'rewrite'=>['slug'=>'tipo-de-edital'],
  ]);
});

add_action('after_switch_theme', function (): void {
  foreach(['Seleção de projetos culturais — Fomento','Credenciamento','Premiação e Subvenção — Cultura Viva','Eventos culturais','Concursos','Licitações e Pregões Eletrônicos','Convocações e Editais Institucionais'] as $category) {
    if (!term_exists($category,'fundart_tipo_edital')) wp_insert_term($category,'fundart_tipo_edital');
  }
  flush_rewrite_rules();
});

function fundart_link(string $path=''): string { return esc_url(home_url('/'.ltrim($path,'/'))); }
function fundart_original(string $path=''): string {
  $url='https://fundart.com.br/'.ltrim($path,'/');
  return esc_url($url);
}
function fundart_home_items(string $post_type,int $limit=4): WP_Query {
  return new WP_Query(['post_type'=>$post_type,'post_status'=>'publish','posts_per_page'=>$limit,'ignore_sticky_posts'=>true]);
}
function fundart_link_current_or_legacy(string $type,string $legacy): string {
  return post_type_exists($type) ? esc_url(get_post_type_archive_link($type) ?: fundart_original($legacy)) : fundart_original($legacy);
}
function fundart_fallback_menu(): void {
  $items=['/' => 'Início','/a-fundart/' => 'A FundArt','/agenda/' => 'Agenda','/oficinas/' => 'Oficinas','/editais/' => 'Editais','/projetos-acoes/' => 'Projetos e Ações','/conselhos/' => 'Conselhos','/transparencia/' => 'Transparência','/ouvidoria/' => 'Ouvidoria Setorial'];
  echo '<ul>';
  foreach ($items as $url=>$label) echo '<li><a href="'.fundart_link($url).'">'.esc_html($label).'</a></li>';
  echo '</ul>';
}
add_filter('nav_menu_link_attributes',function(array $atts): array {
  // O usuário pediu toda navegação na mesma aba, inclusive serviços externos.
  unset($atts['target']);
  return $atts;
});
add_filter('wp_targeted_link_rel',function(string $rel):string {return $rel;});
add_action('customize_register',function(WP_Customize_Manager $c):void {
  $c->add_section('fundart_portal',['title'=>'FUNDART — Destaque da página inicial','priority'=>30]);
  $options=[
    'fundart_hero_title'=>['Título de destaque','Ubatuba em Cena 2026'],
    'fundart_hero_subtitle'=>['Subtítulo','Arte, cultura e diversidade em toda a cidade.'],
    'fundart_hero_url'=>['Link do destaque','/agenda/'],
    'fundart_hero_image'=>['URL da fotografia do destaque',''],
  ];
  foreach($options as $id=>$meta){
    $c->add_setting($id,['default'=>$meta[1],'sanitize_callback'=>$id==='fundart_hero_url'||$id==='fundart_hero_image'?'esc_url_raw':'sanitize_text_field']);
    $c->add_control($id,['label'=>$meta[0],'section'=>'fundart_portal','type'=>'text']);
  }
});
