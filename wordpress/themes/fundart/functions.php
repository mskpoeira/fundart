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
  return new WP_Query(['post_type'=>$post_type,'post_status'=>'publish','posts_per_page'=>$limit,'orderby'=>['date'=>'DESC','ID'=>'DESC'],'ignore_sticky_posts'=>true]);
}
function fundart_link_current_or_legacy(string $type,string $legacy): string {
  return post_type_exists($type) ? esc_url(get_post_type_archive_link($type) ?: fundart_original($legacy)) : fundart_original($legacy);
}
function fundart_fallback_menu(): void {
  $items=['/' => 'Início','/a-fundart/' => 'A FundArt','/agenda/' => 'Agenda','/oficinas/' => 'Oficinas','/editais/' => 'Editais','/projetos-acoes/' => 'Projetos e Ações','/conselhos/' => 'Conselhos','/transparencia/' => 'Transparência','/ouvidoria/' => 'Ouvidoria Setorial','/acervo/' => 'Todas as Páginas'];
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

/** Índice dinâmico do acervo efetivamente publicado no WordPress. */
add_shortcode('fundart_acervo',function():string {
  $search=isset($_GET['acervo_busca'])?sanitize_text_field(wp_unslash($_GET['acervo_busca'])):'';
  $type=isset($_GET['acervo_tipo'])?sanitize_key(wp_unslash($_GET['acervo_tipo'])):'';
  $allowed=['page','post','fundart_edital','fundart_evento','fundart_oficina','fundart_conselho'];
  if(!in_array($type,$allowed,true))$type='';
  $args=['post_type'=>$type?:$allowed,'post_status'=>'publish','posts_per_page'=>60,'paged'=>max(1,(int)(get_query_var('paged')?:($_GET['pagina']??1))),'orderby'=>['date'=>'DESC','ID'=>'DESC'],'ignore_sticky_posts'=>true];
  if($search!=='')$args['s']=$search;
  $query=new WP_Query($args);
  $action=get_permalink();
  $out='<div class="fundart-acervo"><form action="'.esc_url($action).'" method="get" role="search" class="fundart-acervo-busca"><label for="acervo_busca">Pesquisar no acervo</label><input id="acervo_busca" name="acervo_busca" value="'.esc_attr($search).'" placeholder="Nome da página, notícia ou edital"><label for="acervo_tipo">Tipo de conteúdo</label><select id="acervo_tipo" name="acervo_tipo"><option value="">Todos</option>';
  foreach(['page'=>'Páginas e subpáginas','post'=>'Notícias','fundart_edital'=>'Editais','fundart_evento'=>'Eventos','fundart_oficina'=>'Oficinas','fundart_conselho'=>'Conselhos'] as $key=>$value)$out.='<option value="'.esc_attr($key).'" '.selected($type,$key,false).'>'.esc_html($value).'</option>';
  $out.='</select><button type="submit">Pesquisar</button></form><p>'.number_format_i18n((int)$query->found_posts).' resultados publicados</p><div class="fundart-acervo-lista">';
  if(!$query->have_posts())$out.='<p>Nenhum conteúdo encontrado.</p>';
  while($query->have_posts()){
    $query->the_post();
    $out.='<article><span>'.esc_html(get_post_type_object(get_post_type())->labels->singular_name).'</span><h3><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></h3><small>'.esc_html(get_the_date('d/m/Y')).'</small></article>';
  }
  wp_reset_postdata();$out.='</div>';
  $total=(int)$query->max_num_pages;$current=(int)$args['paged'];
  if($total>1){$out.='<nav class="fundart-acervo-paginas" aria-label="Paginação do acervo">';
    if($current>1)$out.='<a href="'.esc_url(add_query_arg(['pagina'=>$current-1,'acervo_busca'=>$search,'acervo_tipo'=>$type],$action)).'">← Anteriores</a>';
    $out.='<span>Página '.esc_html((string)$current).' de '.esc_html((string)$total).'</span>';
    if($current<$total)$out.='<a href="'.esc_url(add_query_arg(['pagina'=>$current+1,'acervo_busca'=>$search,'acervo_tipo'=>$type],$action)).'">Próximos →</a>';
    $out.='</nav>';
  }
  return $out.'</div>';
});
add_action('init',function(){
  if(get_option('fundart_acervo_page_created'))return;
  $page=get_page_by_path('acervo',OBJECT,'page');
  if(!$page){
    $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'Acervo FUNDART — Todas as páginas e notícias','post_name'=>'acervo','post_content'=>'Consulte as páginas, notícias e documentos já publicados no site de testes. O acervo original ainda está em migração. [fundart_acervo]'],true);
    if(is_wp_error($id))return;
  }
  update_option('fundart_acervo_page_created',1,false);
},20);
add_filter('wp_nav_menu_items',function(string $items,$args):string{
  if(isset($args->theme_location)&&$args->theme_location==='principal'&&!str_contains($items,'/acervo/')){
    $items.='<li class="menu-item menu-item-acervo"><a href="'.esc_url(home_url('/acervo/')).'">Todas as páginas</a></li>';
  }
  return $items;
},20,2);

/**
 * Ordenação geral das publicações: novas -> antigas.
 * Aplica-se a notícias, editais, eventos, oficinas, conselhos,
 * pesquisas e arquivos de categorias/taxonomias; páginas institucionais
 * isoladas e menus não são reordenados.
 * A data usada é a publicação (post_date); nunca a data de importação.
 */
add_action('pre_get_posts',static function(WP_Query $query):void {
  if (is_admin() || !$query->is_main_query()) return;
  if (
    $query->is_home() ||
    $query->is_post_type_archive() ||
    $query->is_category() ||
    $query->is_tag() ||
    $query->is_tax() ||
    $query->is_date() ||
    $query->is_author() ||
    $query->is_search()
  ) {
    $query->set('orderby',['date'=>'DESC','ID'=>'DESC']);
    $query->set('order','DESC');
    $query->set('ignore_sticky_posts',true);
  }
});

/**
 * Consultas cronológicas em componentes do tema e páginas especiais.
 * Ordenação estável quando publicações têm a mesma data.
 */
function fundart_consulta_recentes(array $types, int $limit=20, int $page=1):WP_Query {
  return new WP_Query([
    'post_type'=>$types,
    'post_status'=>'publish',
    'posts_per_page'=>max(1,min(100,$limit)),
    'paged'=>max(1,$page),
    'orderby'=>['date'=>'DESC','ID'=>'DESC'],
    'ignore_sticky_posts'=>true
  ]);
}
