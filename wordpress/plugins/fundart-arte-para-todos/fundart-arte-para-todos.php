<?php
/**
 * Plugin Name: FUNDART — Arte Para Todos
 * Description: Painel de pré-cadastros preparatório; edital exige protocolo presencial.
 * Version: 0.1.0
 */
if (!defined('ABSPATH')) exit;
add_action('init', function () {
 register_post_type('fundart_inscricao', [
  'labels'=>['name'=>'Arte Para Todos — Pré-cadastros','singular_name'=>'Pré-cadastro'],
  'public'=>false, 'show_ui'=>true, 'show_in_rest'=>false,
  'menu_icon'=>'dashicons-clipboard', 'supports'=>['title','editor','custom-fields'],
  'capabilities'=>['create_posts'=>'do_not_allow'],'map_meta_cap'=>true
 ]);
 add_shortcode('fundart_arte_para_todos', 'fundart_form_arte');
});
function fundart_form_arte() {
 $pdf='https://fundart.com.br/wp-content/uploads/2026/01/CREDENCIAMENTO-PROJETO-ARTE-PARA-TODOS-2026.pdf';
 $html='<section class="fundart-arte"><h2>Arte para Todos 2026</h2>';
 $html.='<p>Edital nº 30/2025 — Credenciamento nº 03/2025.</p>';
 $html.='<p><strong>Atenção:</strong> o item 9.1 exige entrega presencial dos documentos em envelope lacrado na sede da FUNDART. Nenhuma inscrição oficial é recebida nesta página.</p>';
 $html.='<p><a href="'.esc_url($pdf).'">Baixar edital e ficha de inscrição (PDF)</a></p>';
 $html.='<p>Praça Nóbrega, 54 — Centro, Ubatuba/SP. Atendimento para protocolo: dias úteis, das 8h às 16h, conforme edital.</p>';
 $html.='<p>O formulário eletrônico e a consulta administrativa estão preparados para implantação após autorização da FUNDART e adequação formal das regras.</p></section>';
 return $html;
}
add_action('admin_menu',function () {
 add_submenu_page('edit.php?post_type=fundart_inscricao','Informações do credenciamento','Orientações','manage_options','fundart_arte_orientacoes',function () {
  echo '<div class="wrap"><h1>Credenciamento Arte para Todos 2026</h1><p>O painel destina-se à gestão de inscrições, mediante habilitação futura. O edital vigente estabelece protocolo presencial obrigatório.</p><p>Não inserir documentos com dados pessoais antes de concluir a configuração de segurança e privacidade.</p></div>';
 });
});
