<?php
if(!defined('ABSPATH') || !defined('WP_CLI'))exit;
$pages=[
 'anexo-ii-ficha-inscricao'=>['Anexo II — Ficha de Inscrição','ii'],
 'anexo-iii-plano-trabalho'=>['Anexo III — Plano de Trabalho','iii'],
 'anexo-iv-capacidade-tecnica'=>['Anexo IV — Atestado de Capacidade Técnica','iv'],
 'anexo-v-fato-impeditivo'=>['Anexo V — Declaração de Ausência de Fato Impeditivo','v'],
 'anexo-vi-recurso'=>['Anexo VI — Ficha de Recurso','vi']
];
foreach($pages as $slug=>$data){
 $p=get_page_by_path($slug,OBJECT,'page');
 $content='<p>Modelo eletrônico para preenchimento e exportação, correspondente ao edital de credenciamento Arte para Todos 2026. Os dados são preenchidos localmente no navegador e não constituem protocolo.</p>[fundart_anexo_'.$data[1].']';
 $args=['post_type'=>'page','post_status'=>'publish','post_title'=>$data[0],'post_name'=>$slug,'post_content'=>$content];
 if($p)$args['ID']=$p->ID;
 $id=wp_insert_post($args,true);
 if(is_wp_error($id))WP_CLI::error($slug.' '.$id->get_error_message());
 WP_CLI::log('ANEXO_'.$data[1].'='.$id);
}
$landing=get_page_by_path('credenciamento-arte-educadores',OBJECT,'page');
if($landing){
 $content='<h2>Anexos do edital — preenchimento e impressão</h2><p>Abra individualmente a Ficha de Inscrição, o Plano de Trabalho e os modelos auxiliares. Os formulários podem ser preenchidos no navegador e salvos para assinatura manuscrita ou digital ICP-Brasil em ferramenta externa.</p><ul>';
 foreach($pages as $slug=>$data)$content.='<li><a href="'.esc_url(home_url('/'.$slug.'/')).'">'.esc_html($data[0]).'</a></li>';
 $content.='</ul><p><strong>O edital prevê entrega presencial dos documentos, em envelope lacrado e protocolado na FUNDART, conforme item 9.1.</strong> O preenchimento online não substitui o protocolo.</p>';
 wp_update_post(['ID'=>$landing->ID,'post_content'=>$landing->post_content.$content]);
}
WP_CLI::success('FUNDART_FIVE_ANNEXES_PUBLISHED=YES');
