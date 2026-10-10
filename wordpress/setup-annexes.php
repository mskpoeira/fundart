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

// Anexo I é o projeto/regulamento, não ficha de inscrição.
$anexo_i=get_page_by_path('anexo-i-projeto-arte-para-todos',OBJECT,'page');
$intro='<p>O Anexo I do Edital nº 30/2025 — Credenciamento nº 03/2025 descreve a apresentação, objetivos, democratização do acesso, regulamento geral, polos culturais e linguagens do Projeto Arte para Todos 2026.</p>';
$intro.='<h2>Regulamento e oficinas</h2><p>O projeto contempla atividades descentralizadas em música, teatro, dança, artes plásticas, fotografia, artesanato e folclore/culturas tradicionais e populares. A relação abaixo se refere às propostas de oficinas e às vagas de <strong>credenciamento de profissionais</strong>, não ao número de vagas para alunos.</p>';
$oficinas=[['Bordado','2h','1'],['Capoeira','4h','1'],['Cavaquinho','2h','1'],['Dança (Ballet Clássico)','2h','1'],['Dança (Jazz)','2h','1'],['Danças Étnicas','3h','1'],['Fibras Naturais','2h30','1'],['Piano','1h','1'],['Teatro','3h','1'],['Tecelagem','3h','1'],['Violão','1h','2'],['Proposta Livre','1h','1']];
$intro.='<table><thead><tr><th>Oficina cultural</th><th>Carga horária semanal mínima por turma</th><th>Vagas para credenciamento</th></tr></thead><tbody>';
foreach($oficinas as $row)$intro.='<tr><td>'.esc_html($row[0]).'</td><td>'.esc_html($row[1]).'</td><td>'.esc_html($row[2]).'</td></tr>';
$intro.='</tbody></table><p>A duração prevista na tabela do edital é de até 9 meses. As vagas e oficinas poderão sofrer alteração conforme a demanda e decisão da FUNDART.</p>';
$intro.='<h2>Documentos de inscrição</h2><p><a href="/anexo-ii-ficha-inscricao/">Anexo II — Ficha de Inscrição</a> · <a href="/anexo-iii-plano-trabalho/">Anexo III — Plano de Trabalho</a></p><p><strong>Consulta:</strong> Anexo I nas páginas 18 a 23 do edital original. Consulte o edital integral para obter o texto normativo completo; esta página apresenta um quadro de consulta e não substitui o documento original.</p>';
$anexo_i_data=['post_type'=>'page','post_status'=>'publish','post_name'=>'anexo-i-projeto-arte-para-todos','post_title'=>'Anexo I — Projeto de Oficinas Culturais Arte para Todos','post_content'=>$intro];
if($anexo_i)$anexo_i_data['ID']=$anexo_i->ID;
$id=wp_insert_post($anexo_i_data,true);
if(is_wp_error($id))WP_CLI::error('Anexo I: '.$id->get_error_message());
WP_CLI::log('ANEXO_i='.$id);

$landing=get_page_by_path('credenciamento-arte-educadores',OBJECT,'page');
if($landing){
 $content='<h2>Anexos do edital — preenchimento e impressão</h2><p>Abra individualmente a Ficha de Inscrição, o Plano de Trabalho e os modelos auxiliares. Os formulários podem ser preenchidos no navegador e salvos para assinatura manuscrita ou digital ICP-Brasil em ferramenta externa.</p><ul>';
 $content.='<li><a href="'.esc_url(home_url('/anexo-i-projeto-arte-para-todos/')).'">Anexo I — Projeto de Oficinas Culturais Arte para Todos</a></li>';
 foreach($pages as $slug=>$data)$content.='<li><a href="'.esc_url(home_url('/'.$slug.'/')).'">'.esc_html($data[0]).'</a></li>';
 $content.='</ul><p><strong>O edital prevê entrega presencial dos documentos, em envelope lacrado e protocolado na FUNDART, conforme item 9.1.</strong> O preenchimento online não substitui o protocolo.</p>';
 wp_update_post(['ID'=>$landing->ID,'post_content'=>preg_replace('~<h2>Anexos do edital — preenchimento e impressão</h2>.*$~s','',$landing->post_content).$content]);
}
WP_CLI::success('FUNDART_FIVE_ANNEXES_PUBLISHED=YES');
