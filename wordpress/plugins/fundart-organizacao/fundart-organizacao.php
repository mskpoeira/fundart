<?php
/**
 * Plugin Name: FUNDART — Organização do Acervo
 * Description: Índice de editais, licitações, convocações e demais publicações, com classificação prudente.
 * Version: 1.0.0
 */
defined('ABSPATH')||exit;
function fdo_groups():array {return [
 'editais'=>['Editais e Chamamentos','/editais/'],
 'licitacoes'=>['Licitações e Pregões','/editais/licitacoes/'],
 'convocacoes'=>['Convocações','/editais/convocacoes/'],
 'credenciamentos'=>['Credenciamentos','/editais/credenciamentos/'],
 'concursos'=>['Concursos e Seleções','/editais/concursos/'],
 'fomento'=>['Fomento e Premiações','/editais/fomento/'],
 'institucionais'=>['Publicações Institucionais','/editais/institucionais/'],
 'noticias'=>['Notícias','/noticias/'],
 'eventos'=>['Eventos','/agenda/'],
 'oficinas'=>['Oficinas Culturais','/oficinas/'],
 'projetos'=>['Projetos Culturais','/projetos-acoes/'],
 'conselhos'=>['Conselhos','/conselhos/'],
 'transparencia'=>['Transparência','/transparencia/'],
 'ouvidoria'=>['Ouvidoria Setorial','/ouvidoria/']
];}
function fdo_classify(string $title):string{
 $t=remove_accents(mb_strtolower($title,'UTF-8'));
 if(preg_match('/licitac|pregao|concorrencia|dispensa de licitac|tomada de prec|leilao|chamamento para concessao/',$t))return 'licitacoes';
 if(preg_match('/convocac|convocado|convocados|chamada de aprovados/',$t))return 'convocacoes';
 if(preg_match('/credenciam/',$t))return 'credenciamentos';
 if(preg_match('/concurso|processo seletivo|selecao de estagi|pss /',$t))return 'concursos';
 if(preg_match('/premiac|fomento|cultura viva|aldir blanc|pnab|paulo gustavo/',$t))return 'fomento';
 if(preg_match('/edital|chamamento|retificac|homologac|resultado final|termo aditivo|aditivo/',$t))return 'institucionais';
 return '';
}
function fdo_listing(string $group):string{
 $groups=fdo_groups();if(!isset($groups[$group]))return '';
 $types=$group==='noticias'?['post']:($group==='eventos'?['fundart_evento']:($group==='oficinas'?['fundart_oficina']:($group==='conselhos'?['fundart_conselho']:['fundart_edital','post']));
 $page=max(1,min(9999,(int)($_GET['pagina']??1)));
 $q=new WP_Query(['post_type'=>$types,'post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','orderby'=>['date'=>'DESC','ID'=>'DESC'],'ignore_sticky_posts'=>true,'no_found_rows'=>true]);
 $ids=[];
 foreach($q->posts as $id){
  $type=get_post_type($id);$category=fdo_classify(get_the_title($id));
  if($group==='editais' && ($type==='fundart_edital'||$category))$ids[]=$id;
  elseif(in_array($group,['licitacoes','convocacoes','credenciamentos','concursos','fomento','institucionais'],true)&&($category===$group))$ids[]=$id;
  elseif(in_array($group,['noticias','eventos','oficinas','conselhos'],true))$ids[]=$id;
 }
 $total=count($ids);$perPage=20;$maxPage=max(1,(int)ceil($total/$perPage));
 $page=min($page,$maxPage);$subset=array_slice($ids,($page-1)*$perPage,$perPage);
 $out='<div class="fdo-directory"><p>'.number_format_i18n($total).' publicações nesta seção, das mais recentes para as mais antigas.</p>';
 if(!$subset)$out.='<p>Ainda não há publicações classificadas nesta seção. Consulte o acervo e os documentos históricos para verificar outros registros.</p>';
 foreach($subset as $id){
  $out.='<article class="fdo-entry"><time datetime="'.esc_attr(get_the_date('Y-m-d',$id)).'">'.esc_html(get_the_date('d/m/Y',$id)).'</time><h3><a href="'.esc_url(get_permalink($id)).'">'.esc_html(get_the_title($id)).'</a></h3><span>'.esc_html(get_post_type($id)==='fundart_edital'?'Edital':'Publicação do acervo').'</span></article>';
 }
 if($maxPage>1){
  $url=get_permalink();
  $out.='<nav class="fdo-pages" aria-label="Paginação"><span>Página '.esc_html((string)$page).' de '.esc_html((string)$maxPage).'</span> ';
  if($page>1)$out.='<a href="'.esc_url(add_query_arg('pagina',$page-1,$url)).'">← Mais recentes</a> ';
  if($page<$maxPage)$out.='<a href="'.esc_url(add_query_arg('pagina',$page+1,$url)).'">Mais antigas →</a>';
  $out.='</nav>';
 }
 return $out.'</div>';
}
add_shortcode('fundart_publicacoes',static function($atts):string{
 $atts=shortcode_atts(['grupo'=>'editais'],$atts,'fundart_publicacoes');
 return fdo_listing(sanitize_key($atts['grupo']));
});
add_action('wp_enqueue_scripts',static function(){
 wp_register_style('fdo-style',false,[],'1.0');wp_enqueue_style('fdo-style');
 wp_add_inline_style('fdo-style','.fdo-directory{max-width:1000px}.fdo-entry{padding:18px 0;border-bottom:1px solid #d3dce8}.fdo-entry time{color:#576f84;font-size:.85rem}.fdo-entry h3{margin:5px 0}.fdo-entry a{color:#07588d}.fdo-entry span{font-size:.8rem;color:#536374}.fdo-pages{display:flex;gap:15px;align-items:center;margin:24px 0;flex-wrap:wrap}');
});
