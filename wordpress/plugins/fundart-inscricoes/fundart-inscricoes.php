<?php
/**
 * Plugin Name: FUNDART — Inscrições de Oficinas (Homologação)
 * Description: Formulários separados para oficinas e pré-cadastro de arte-educadores; envios de homologação, sem validade de inscrição oficial.
 * Version: 0.2.0
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
function fundart_modalidades_2026(): array { return ['Dança (Jazz)','Bordado','Tecelagem','Cestaria','Artesanato','Fibras Naturais','Plantas Alimentícias Não Convencionais (PANC)','Desenho','Piano','Cavaquinho','Violão']; }
function fundart_inscricao_form(string $tipo): string {
 $aluno=$tipo==='aluno';
 $action=$aluno?'fundart_oficina_aluno':'fundart_oficina_educador';
 $title=$aluno?'Interesse em oficina cultural':'Pré-cadastro de arte-educador';
 ob_start();
 ?>
 <section class="fundart-enrollment" aria-label="<?php echo esc_attr($title); ?>">
 <div class="notice"><strong>Ambiente de testes.</strong> Os campos de cadastro completo estão em preparação. <strong>O envio de dados de alunos está temporariamente desabilitado</strong> até a aprovação institucional e a adequação da proteção dos dados pessoais. Não informe CPF, RG, endereço ou dados reais de menores neste ambiente de testes.</div>
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
  <label>Nome completo do aluno <input required maxlength="120" name="nome" autocomplete="off" placeholder="Nome do aluno"></label>
  <label>Modalidade desejada <select required name="oficina"><option value="">Selecione a modalidade</option><?php foreach(fundart_modalidades_2026() as $modalidade): ?><option value="<?php echo esc_attr($modalidade); ?>"><?php echo esc_html($modalidade); ?></option><?php endforeach; ?></select></label>
  <label>CPF do aluno <input name="cpf" inputmode="numeric" pattern="[0-9. -]{11,14}" placeholder="000.000.000-00" autocomplete="off" required></label>
  <label>RG do aluno <input name="rg" maxlength="30" autocomplete="off" required></label>
  <label>Data de nascimento <input type="date" name="nascimento" max="<?php echo esc_attr(wp_date('Y-m-d')); ?>" required></label>
  <label>Idade (calculada automaticamente) <input name="idade_visual" readonly type="number" min="0" max="120" aria-label="Idade calculada"></label>
  <label>Telefone com DDD <input name="telefone" type="tel" maxlength="20" autocomplete="off" required></label>
  <label>E-mail para contato <input name="email" type="email" maxlength="150" autocomplete="off" required></label>
  <label>CEP <input name="cep" inputmode="numeric" maxlength="9" autocomplete="off" required></label>
  <label>Logradouro <input name="logradouro" maxlength="160" autocomplete="off" required></label>
  <label>Número <input name="numero" maxlength="20" autocomplete="off" required></label>
  <label>Complemento <input name="complemento" maxlength="100" autocomplete="off"></label>
  <label>Bairro <input name="bairro" maxlength="100" autocomplete="off" required></label>
  <label>Cidade <input name="cidade" maxlength="100" value="Ubatuba" autocomplete="off" required></label>
  <label>UF <select name="uf" required><option value="SP">São Paulo</option><option value="RJ">Rio de Janeiro</option><option value="MG">Minas Gerais</option><option value="PR">Paraná</option><option value="outros">Outra UF</option></select></label>
  <fieldset class="fundart-responsavel" data-minor-fields hidden><legend>Responsável legal — obrigatório para menores de 18 anos</legend>
    <label>Nome completo do responsável <input name="responsavel" maxlength="120" autocomplete="off"></label>
    <label>CPF do responsável <input name="responsavel_cpf" inputmode="numeric" maxlength="14" autocomplete="off"></label>
    <label>RG do responsável <input name="responsavel_rg" maxlength="30" autocomplete="off"></label>
    <label>Telefone com DDD do responsável <input name="responsavel_telefone" type="tel" maxlength="20" autocomplete="off"></label>
    <label>E-mail do responsável <input name="responsavel_email" type="email" maxlength="150" autocomplete="off"></label>
    <label>Grau de parentesco / vínculo legal <select name="parentesco"><option value="">Selecione</option><option value="Mãe">Mãe</option><option value="Pai">Pai</option><option value="Avó/Avô">Avó/Avô</option><option value="Tutor(a)">Tutor(a)</option><option value="Guardião(ã)">Guardião(ã)</option><option value="Outro responsável legal">Outro responsável legal</option></select></label>
  </fieldset>
  <p class="fundart-form-note">Modalidades extraídas da programação FUNDART de 2026. A disponibilidade de turmas e vagas depende de confirmação da Fundação. Não utilize dados pessoais reais enquanto este ambiente estiver em homologação.</p>
 <?php else: ?>
  <label>Nome fictício / nome fantasia para teste <input required maxlength="120" name="nome" autocomplete="off" placeholder="Ex.: Arte Educador Teste"></label>
  <label>Área artística ou oficina proposta <input required maxlength="120" name="oficina" placeholder="Ex.: Artes visuais"></label>
  <label>Tipo de proponente <select name="proponente" required><option value="">Selecione</option><option value="MEI">MEI</option><option value="Pessoa jurídica">Pessoa jurídica</option></select></label>
  <label>E-mail de teste <input required type="email" maxlength="150" name="email" autocomplete="off" placeholder="teste@exemplo.com"></label>
  <p class="fundart-form-note">O Edital nº 30/2025 — Credenciamento nº 03/2025 exige entrega presencial da documentação, conforme item 9.1. Este formulário não substitui o procedimento previsto no edital.</p>
 <?php endif; ?>
 </div>
 <label class="fundart-form-consent"><input type="checkbox" name="ciente" value="1" required> Declaro ciência de que este formulário é exclusivamente de homologação e não constitui inscrição oficial.</label>
 <?php if ($aluno): ?><button type="button" class="btn" disabled aria-disabled="true">Inscrições online aguardando autorização</button><?php else: ?><button type="submit" class="btn">Enviar teste</button><?php endif; ?>
 </form>
 </section>
 <?php return (string)ob_get_clean();
}
add_action('wp_footer',function(){ ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
 document.querySelectorAll('.fundart-enrollment form').forEach(function(form){
  var birth=form.querySelector('[name="nascimento"]'), age=form.querySelector('[name="idade_visual"]'), section=form.querySelector('[data-minor-fields]');
  if(!birth||!age||!section)return;
  var fields=section.querySelectorAll('input,select');
  function update(){
   var b=birth.value?new Date(birth.value+'T12:00:00'):null, today=new Date(), years=NaN;
   if(b&&!isNaN(b.getTime())&&b<=today){
    years=today.getFullYear()-b.getFullYear();
    if(today.getMonth()<b.getMonth()||(today.getMonth()===b.getMonth()&&today.getDate()<b.getDate()))years--;
   }
   age.value=Number.isFinite(years)?String(years):'';
   var minor=Number.isFinite(years)&&years<18;
   section.hidden=!minor;
   fields.forEach(function(el){el.required=minor});
  }
  birth.addEventListener('change',update);update();
 });
});
</script>
<?php },20);
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
  $birth=sanitize_text_field(wp_unslash($_POST['nascimento']??''));
  $birthdate=DateTimeImmutable::createFromFormat('!Y-m-d',$birth);
  if(!$birthdate || $birthdate->format('Y-m-d')!==$birth || $birthdate>new DateTimeImmutable('today')) $fail();
  $faixa=($birthdate->diff(new DateTimeImmutable('today'))->y<18)?'menor':'adulto';
  $responsavel=sanitize_text_field(wp_unslash($_POST['responsavel']??''));
  if(!in_array($oficina,fundart_modalidades_2026(),true) || ($faixa==='menor' && ($responsavel==='' || empty($_POST['responsavel_telefone']) || empty($_POST['parentesco'])))) $fail();
  $data['faixa']=$faixa;
  $data['responsavel']=$faixa==='menor'?$responsavel:'';
 }else{
  $proponente=sanitize_text_field(wp_unslash($_POST['proponente']??''));
  if(!in_array($proponente,['MEI','Pessoa jurídica'],true)) $fail();
  $data['proponente']=$proponente;
 }
 // Suspenso para dados pessoais reais: a operação oficial requer autorização institucional,
 // política de privacidade publicada, retenção definida e infraestrutura protegida.
 if($tipo==='aluno') $fail();
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
