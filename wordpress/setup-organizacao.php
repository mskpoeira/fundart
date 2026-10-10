<?php
/** Navegacao FUNDART -- setup idempotente. Nao sobrescreve paginas ja personalizadas. */
if(!defined('ABSPATH')||!defined('WP_CLI'))exit(1);
$structure=[
 'editais'=>['Editais e Chamamentos','Consulte os editais, chamamentos, licitações, convocações, credenciamentos, concursos e instrumentos de fomento. A classificação inicial é temática e deve ser conferida com o documento original.','editais',0],
 'editais/licitacoes'=>['Licitações e Pregões','Publicações relacionadas a procedimentos licitatórios, pregões e contratações. Confira cada documento oficial para identificar sua modalidade e situação.','licitacoes','editais'],
 'editais/convocacoes'=>['Convocações','Publicações identificadas como convocações e chamamentos de candidatos, classificados ou interessados.','convocacoes','editais'],
 'editais/credenciamentos'=>['Credenciamentos','Publicações relacionadas a credenciamentos culturais.','credenciamentos','editais'],
 'editais/concursos'=>['Concursos e Processos Seletivos','Editais de concursos culturais e seleções públicas.','concursos','editais'],
 'editais/fomento'=>['Fomento Cultural e Premiações','Publicações relativas a fomento cultural, premiações e políticas de incentivo.','fomento','editais'],
 'editais/institucionais'=>['Editais Institucionais','Editais, aditivos, retificações e outros atos institucionais que aguardam confirmação de classificação.','institucionais','editais']
];
$created=0;$existing=0;$ids=[];
foreach($structure as $path=>[$title,$intro,$group,$parentSlug]){
 $prior=get_page_by_path($path,OBJECT,'page');
 if($prior){$ids[$path]=$prior->ID;$existing++;continue;}
 $parent=$parentSlug?get_page_by_path($parentSlug,OBJECT,'page'):null;
 if($parentSlug&&!$parent){WP_CLI::warning('Parent missing '.$path);continue;}
 $content='<p>'.esc_html($intro).'</p>';
 if($group==='editais'){
  $content.='<div class="fundart-organizacao-links"><ul>';
  foreach(['licitacoes'=>'Licitações e Pregões','convocacoes'=>'Convocações','credenciamentos'=>'Credenciamentos','concursos'=>'Concursos e Seleções','fomento'=>'Fomento e Premiações','institucionais'=>'Editais Institucionais'] as $key=>$label)
   $content.='<li><a href="'.esc_url(home_url('/editais/'.$key.'/')).'">'.esc_html($label).'</a></li>';
  $content.='</ul></div>';
 }
 $content.='[fundart_publicacoes grupo="'.$group.'"]';
 $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>$title,'post_name'=>basename($path),'post_parent'=>$parent?(int)$parent->ID:0,'post_content'=>$content],true);
 if(is_wp_error($id)){WP_CLI::warning($path.': '.$id->get_error_message());continue;}
 $ids[$path]=$id;$created++;WP_CLI::log('PAGE_NEW '.$path.' ID='.$id);
}
$landing=get_page_by_path('editais',OBJECT,'page');
if($landing){
 $text=(string)$landing->post_content;
 if(!str_contains($text,'fundart_publicacoes')){
  $nav='<h2>Consulte por modalidade</h2><ul>';
  foreach(['licitacoes'=>'Licitações e Pregões','convocacoes'=>'Convocações','credenciamentos'=>'Credenciamentos','concursos'=>'Concursos e Processos Seletivos','fomento'=>'Fomento e Premiações','institucionais'=>'Editais Institucionais'] as $key=>$label)
   $nav.='<li><a href="'.esc_url(home_url('/editais/'.$key.'/')).'">'.esc_html($label).'</a></li>';
  $nav.='</ul><h2>Publicações</h2>[fundart_publicacoes grupo="editais"]';
  wp_update_post(['ID'=>$landing->ID,'post_content'=>$text.'\n'.$nav]);
  WP_CLI::log('EDITAIS_LANDING_NAV=ADDED');
 }
}
$menu=wp_get_nav_menu_object('Menu principal FUNDART');
$menu_added=0;
if($menu&&!is_wp_error($menu)){
 $entries=wp_get_nav_menu_items($menu->term_id)?:[];
 $existingMenu=[];$parentMenu=0;
 foreach($entries as $i){
  if($i->object==='page')$existingMenu[(int)$i->object_id]=(int)$i->ID;
  if($landing && $i->object==='page' && (int)$i->object_id===(int)$landing->ID)$parentMenu=(int)$i->ID;
 }
 foreach(['editais/licitacoes'=>'Licitações','editais/convocacoes'=>'Convocações','editais/credenciamentos'=>'Credenciamentos','editais/concursos'=>'Concursos e Seleções','editais/fomento'=>'Fomento','editais/institucionais'=>'Editais Institucionais'] as $path=>$label){
  $p=get_page_by_path($path,OBJECT,'page');
  if(!$p||isset($existingMenu[(int)$p->ID]))continue;
  $item=wp_update_nav_menu_item($menu->term_id,0,['menu-item-title'=>$label,'menu-item-object'=>'page','menu-item-object-id'=>$p->ID,'menu-item-type'=>'post_type','menu-item-parent-id'=>$parentMenu,'menu-item-status'=>'publish']);
  if(!is_wp_error($item))$menu_added++;
 }
}
WP_CLI::success('ORGANIZACAO_METRICS='.wp_json_encode(['created'=>$created,'existing'=>$existing,'menu_added'=>$menu_added,'landing'=>!!$landing],JSON_UNESCAPED_UNICODE));
