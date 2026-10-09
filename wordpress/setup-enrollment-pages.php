<?php
if (!defined('ABSPATH') || !defined('WP_CLI')) exit;
$pages=[
 'inscricoes-oficinas'=>[
  'Inscrições em Oficinas Culturais',
  '<p>Área de informações e testes do sistema de inscrições nas oficinas culturais da FUNDART, incluindo o projeto Arte para Todos 2026.</p><h2>Formulário para alunos e participantes</h2><p>Faça uma simulação de interesse em oficinas. <strong>Não utilize dados pessoais verdadeiros nesta fase de homologação.</strong></p>[fundart_inscricao_aluno]<h2>Arte para Todos — sou arte-educador</h2><p>Se você pretende apresentar uma proposta para ministrar oficinas, consulte a área específica de credenciamento.</p><p><a class="btn secondary" href="/credenciamento-arte-educadores/">Abrir formulário de pré-cadastro de arte-educador</a></p>'
 ],
 'credenciamento-arte-educadores'=>[
  'Arte para Todos 2026 — Pré-cadastro de Arte-Educadores',
  '<p>O credenciamento de prestadores para ministrar oficinas é um procedimento diferente da inscrição de alunos.</p><div class="notice"><strong>Regra do edital vigente:</strong> a documentação dos proponentes deve ser protocolada presencialmente, conforme o item 9.1 do Edital nº 30/2025 — Credenciamento nº 03/2025. O formulário abaixo é apenas de teste e não produz efeitos no credenciamento.</div><h2>Formulário de pré-cadastro — homologação</h2>[fundart_pre_cadastro_educador]<p><a href="https://fundart.com.br/edital/edital-no-62-2025-credenciamento-no-03-2025-projeto-de-oficinas-culturais-arte-para-todos-2026/">Consultar a publicação oficial do edital e eventuais documentos anexos</a></p><p><a href="/inscricoes-oficinas/">Voltar à inscrição de alunos nas oficinas</a></p>'
 ]
];
foreach ($pages as $slug=>$data) {
 $page=get_page_by_path($slug,OBJECT,'page');
 $args=['post_type'=>'page','post_status'=>'publish','post_name'=>$slug,'post_title'=>$data[0],'post_content'=>$data[1]];
 if($page)$args['ID']=$page->ID;
 $id=wp_insert_post($args,true);
 if(is_wp_error($id))WP_CLI::error($slug.': '.$id->get_error_message());
 WP_CLI::log($slug.'='.$id);
}
WP_CLI::success('TWO_ENROLLMENT_PAGES=OK');
