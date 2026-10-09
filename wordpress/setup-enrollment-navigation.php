<?php
if(!defined('ABSPATH') || !defined('WP_CLI')) exit;
$landing=get_page_by_path('oficinas',OBJECT,'page');
if(!$landing) WP_CLI::error('Pagina oficinas ausente');
$content='<p>Conheça as oficinas culturais da FUNDART e as oportunidades do Arte para Todos. Escolha abaixo o formulário de acordo com seu interesse.</p><div class="enrollment-links"><a href="/inscricoes-oficinas/">🎭 Sou aluno — interesse em oficinas culturais</a><a href="/credenciamento-arte-educadores/">🎨 Sou arte-educador — pré-cadastro Arte para Todos</a></div><div class="notice"><strong>Formulários em ambiente de testes.</strong> Os envios não constituem matrícula, credenciamento ou protocolo oficial. O credenciamento de arte-educadores segue as disposições presenciais do edital vigente.</div><p><a href="/arte-para-todos-2026/">Consulte as informações e o edital do Arte para Todos 2026</a>.</p>';
wp_update_post(['ID'=>$landing->ID,'post_content'=>$content]);
$menu=wp_get_nav_menu_object('Menu principal FUNDART');
if($menu&&!is_wp_error($menu)){
 $items=wp_get_nav_menu_items($menu->term_id)?:[];
 $existing=[];
 $parent=0;
 foreach($items as $i){
  $existing[(int)$i->object_id]=true;
  if((int)$i->object_id===(int)$landing->ID)$parent=(int)$i->ID;
 }
 foreach(['inscricoes-oficinas'=>'Inscrição em Oficinas','credenciamento-arte-educadores'=>'Arte para Todos — Arte-Educadores'] as $slug=>$label){
  $page=get_page_by_path($slug,OBJECT,'page');
  if(!$page)WP_CLI::error('Pagina ausente: '.$slug);
  if(!isset($existing[(int)$page->ID])){
   wp_update_nav_menu_item($menu->term_id,0,['menu-item-title'=>$label,'menu-item-object'=>'page','menu-item-object-id'=>$page->ID,'menu-item-type'=>'post_type','menu-item-parent-id'=>$parent,'menu-item-status'=>'publish']);
  }
 }
}
WP_CLI::success('ENROLLMENT_NAV=OK');
