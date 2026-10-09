<?php
/**
 * Plugin Name: FUNDART — Inscrições de Oficinas (Homologação)
 * Description: Formulários separados para oficinas e pré-cadastro de arte-educadores; envios de homologação, sem validade de inscrição oficial.
 * Version: 0.1.0
 * Requires PHP: 8.1
 */
if (!defined('ABSPATH')) exit;
add_action('init', function () {
 register_post_type('fundart_inscricao', [
  'labels'=>['name'=>'Inscrições de teste','singular_name'=>'Inscrição de teste','menu_name'=>'Inscrições (teste)'],
  'public'=>false,'publicly_queryable'=>false,'exclude_from_search'=>true,'show_ui'=>true,'show_in_menu'=>true,'show_in_rest'=>false,
  'capability_type'=>'post','map_meta_cap'=>true,'supports'=>['title'],'menu_icon'=>'dashicons-forms',
 ]);
});
function fundart_inscricao_form(string $tipo): string {
 $aluno=$tipo==='aluno';
 $action=$aluno?'fundart_oficina_aluno':'fundart_oficina_educador';
 $title=$aluno?'Interesse em oficina cultural':'Pré-cadastro de arte-educador';
 ob_start();
 ?>
 <section class="fundart-enrollment" aria-label="<?php echo esc_attr($title); ?>">
 <div class="notice"><strong>Ambiente de testes.</strong> O envio deste formulário registra uma simulação e <strong>não</strong> efetiva matrícula, credenciamento ou protocolo oficial. Não informe CPF, documentos ou dados reais de menores nesta fase.</div>
 <?php if(isset($_GET['fundart_envio'])):
 $status=sanitize_key(wp_unslash($_GET['fundart_envio']));
 if($status==='ok'): ?><p class="fundart-form-message" role="status">Envio de teste registrado. Este registro não vale como inscrição oficial.</p>
 <?php elseif($status==='erro'): ?><p class="fundart-form-error" role="alert">Não foi possível registrar o envio. Confira os campos e tente novamente.</p><?php endif;endif; ?>
 <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fundart-form">
 <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
 <?php wp_nonce_field('fundart_form_'.$tipo,'fundart_nonce'); ?>
 <div class="fundart-hp" aria-hidden="true"><label>Deixe vazio<input type="text" name="fundart_website" tabindex="-1" autocomplete="off"></label></div>
 <div class="fundart-form-fields">
 <?php if($aluno): ?>
  <label>Nome fictício para teste <input required maxlength="90" name="nome" autocomplete="off" placeholder="Ex.: Participante Teste"></label>
  <label>Modalidade pretendida <input required maxlength="120" name="oficina" placeholder="Ex.: Música, teatro, dança"></label>
  <label>Faixa etária <select name="faixa" required><option value="">Selecione</option><option value="menor">Menor de 18 anos</option><option value="adulto">18 anos ou mais</option></select></label>
  <label>Contato de teste (e-mail) <input required type="email" maxlength="150" name="email" placeholder="teste@exemplo.com" autocomplete="off"></label>
  <label>Responsável legal (apenas se menor; use nome fictício) <input maxlength="90" name="responsavel" autocomplete="off"></label>
 <?php else: ?>
  <label>Nome fictício / nome fantasia para teste <input required maxlength="120" name="nome" autocomplete="off" placeholder="Ex.: Arte Educador Teste"></label>
  <label>Área artística ou oficina proposta <input required maxlength="120" name="oficina" placeholder="Ex.: Artes visuais"></label>
  <label>Tipo de proponente <select name="proponente" required><option value="">Selecione</option><option value="MEI">MEI</option><option value="Pessoa jurídica">Pessoa jurídica</option></select></label>
  <label>E-mail de teste <input required type="email" maxlength="150" name="email" autocomplete="off" placeholder="teste@exemplo.com"></label>
  <p class="fundart-form-note">O Edital nº 30/2025 — Credenciamento nº 03/2025 exige entrega presencial da documentação, conforme item 9.1. Este formulário não substitui o procedimento previsto no edital.</p>
 <?php endif; ?>
 </div>
 <label class="fundart-form-consent"><input type="checkbox" name="ciente" value="1" required> Declaro ciência de que este formulário é exclusivamente de homologação e não constitui inscrição oficial.</label>
 <button type="submit" class="btn">Enviar teste</button>
 </form>
 </section>
 <?php return (string)ob_get_clean();
}
add_shortcode('fundart_inscricao_aluno',static fn() => fundart_inscricao_form('aluno'));
add_shortcode('fundart_pre_cadastro_educador',static fn() => fundart_inscricao_form('educador'));

function fundart_receber_inscricao(string $tipo): void {
 $redirect=$tipo==='aluno'?home_url('/inscricoes-oficinas/'):home_url('/credenciamento-arte-educadores/');
 $fail=static function()use($redirect){wp_safe_redirect(add_query_arg('fundart_envio','erro',$redirect),303);exit;};
 if(strtoupper($_SERVER['REQUEST_METHOD']??'')!=='POST') $fail();
 if(!isset($_POST['fundart_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['fundart_nonce'])),'fundart_form_'.$tipo)) $fail();
 if(!empty($_POST['fundart_website']) || ($_POST['ciente']??'')!=='1') $fail();
 $nome=sanitize_text_field(wp_unslash($_POST['nome']??''));
 $oficina=sanitize_text_field(wp_unslash($_POST['oficina']??''));
 $email=sanitize_email(wp_unslash($_POST['email']??''));
 if(!$nome || !$oficina || !is_email($email) || mb_strlen($nome)>120 || mb_strlen($oficina)>120) $fail();
 $data=['tipo'=>$tipo,'nome'=>$nome,'oficina'=>$oficina,'email'=>$email,'ambiente'=>'homologacao'];
 if($tipo==='aluno'){
  $faixa=sanitize_key(wp_unslash($_POST['faixa']??''));
  $responsavel=sanitize_text_field(wp_unslash($_POST['responsavel']??''));
  if(!in_array($faixa,['menor','adulto'],true) || ($faixa==='menor' && $responsavel==='')) $fail();
  $data['faixa']=$faixa;
  $data['responsavel']=$faixa==='menor'?$responsavel:'';
 }else{
  $proponente=sanitize_text_field(wp_unslash($_POST['proponente']??''));
  if(!in_array($proponente,['MEI','Pessoa jurídica'],true)) $fail();
  $data['proponente']=$proponente;
 }
 $id=wp_insert_post(['post_type'=>'fundart_inscricao','post_status'=>'private','post_title'=>($tipo==='aluno'?'Aluno / ':'Arte-educador / ').wp_date('d/m/Y H:i:s'),'post_content'=>''],true);
 if(is_wp_error($id)) $fail();
 foreach($data as $key=>$value)update_post_meta($id,'_fundart_'.$key,$value);
 wp_safe_redirect(add_query_arg('fundart_envio','ok',$redirect),303);exit;
}
foreach(['aluno'=>'fundart_oficina_aluno','educador'=>'fundart_oficina_educador'] as $tipo=>$action){
 add_action('admin_post_nopriv_'.$action,static fn() => fundart_receber_inscricao($tipo));
 add_action('admin_post_'.$action,static fn() => fundart_receber_inscricao($tipo));
}
add_action('add_meta_boxes_fundart_inscricao',function(){
 add_meta_box('fundart_dados','Dados do envio de homologação',function($post){
  foreach(['tipo','nome','oficina','email','faixa','responsavel','proponente','ambiente'] as $key){
   $value=get_post_meta($post->ID,'_fundart_'.$key,true);
   if($value!=='')echo '<p><strong>'.esc_html(ucfirst($key)).':</strong> '.esc_html($value).'</p>';
  }
 },'fundart_inscricao','normal','high');
});
