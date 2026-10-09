<?php
/**
 * Plugin Name: FUNDART — Gestão Cultural
 * Description: Painel administrativo de cursos, turmas, professores, projetos e inscrições em homologação.
 * Version: 0.1.0
 */
defined('ABSPATH') || exit;

function fgc_types(): array {
 return [
  'fgc_curso'=>['Cursos','Curso','dashicons-welcome-learn-more'],
  'fgc_turma'=>['Turmas','Turma','dashicons-groups'],
  'fgc_professor'=>['Professores','Professor','dashicons-businessperson'],
  'fgc_projeto'=>['Projetos culturais','Projeto','dashicons-art'],
 ];
}
add_action('init',function(){
 foreach(fgc_types() as $type=>$cfg){
  register_post_type($type,['labels'=>['name'=>$cfg[0],'singular_name'=>$cfg[1]],'public'=>false,'show_ui'=>true,
   'show_in_menu'=>'fundart-gestao','supports'=>['title','editor','revisions'],
   'capability_type'=>['fgc_item','fgc_items'],'map_meta_cap'=>true,'capabilities'=>[
    'edit_post'=>'edit_fgc_item','read_post'=>'read_fgc_item','delete_post'=>'delete_fgc_item',
    'edit_posts'=>'edit_fgc_items','edit_others_posts'=>'edit_others_fgc_items',
    'publish_posts'=>'publish_fgc_items','read_private_posts'=>'read_private_fgc_items',
    'delete_posts'=>'delete_fgc_items','delete_others_posts'=>'delete_others_fgc_items',
    'edit_published_posts'=>'edit_published_fgc_items','delete_published_posts'=>'delete_published_fgc_items',
    'create_posts'=>'edit_fgc_items',
   ],'menu_icon'=>$cfg[2]]);
 }
});
function fgc_caps(): array {
 return ['read','edit_fgc_item','read_fgc_item','delete_fgc_item','edit_fgc_items','edit_others_fgc_items',
 'publish_fgc_items','read_private_fgc_items','delete_fgc_items','delete_others_fgc_items',
 'edit_published_fgc_items','delete_published_fgc_items'];
}
register_activation_hook(__FILE__,function(){
 foreach(['administrator'=>fgc_caps(),'fundart_master'=>fgc_caps(),'fundart_gestor'=>fgc_caps(),
  'fundart_coordenador'=>['read','edit_fgc_item','read_fgc_item','edit_fgc_items','edit_published_fgc_items'],
  'fundart_professor'=>['read']] as $name=>$caps){
  $role=get_role($name);
  if(!$role) $role=add_role($name,match($name){'fundart_master'=>'Master FUNDART','fundart_gestor'=>'Gestor Cultural','fundart_coordenador'=>'Coordenador de Cursos','fundart_professor'=>'Professor',default=>'Administrador'},[]);
  if($role)foreach($caps as $cap)$role->add_cap($cap);
 }
 // Existing WordPress administrator retains custody; no new account or published master credentials.
});
add_action('admin_menu',function(){
 add_menu_page('Gestão Cultural FUNDART','Gestão Cultural','edit_fgc_items','fundart-gestao','fgc_dashboard','dashicons-chart-area',24);
 add_submenu_page('fundart-gestao','Painel Geral','Painel Geral','edit_fgc_items','fundart-gestao','fgc_dashboard');
 add_submenu_page('fundart-gestao','Arte para Todos','Arte para Todos','edit_fgc_items','fundart-arte-para-todos',function(){fgc_dashboard('arte-para-todos');});
 add_submenu_page('fundart-gestao','Outros Projetos','Outros Projetos','edit_fgc_items','fundart-outros-projetos',function(){fgc_dashboard('outros');});
 add_submenu_page('fundart-gestao','Inscrições (teste)','Inscrições (teste)','edit_fgc_items','fundart-inscricoes-gestao','fgc_inscricoes');
});
function fgc_count(string $type,string $status='publish'): int {
 $c=wp_count_posts($type);return isset($c->$status)?(int)$c->$status:0;
}
function fgc_dashboard(string $filter=''): void {
 if(!current_user_can('edit_fgc_items'))wp_die('Acesso não autorizado.');
 $items=['Cursos'=>fgc_count('fgc_curso'),'Turmas'=>fgc_count('fgc_turma'),'Professores'=>fgc_count('fgc_professor'),'Projetos'=>fgc_count('fgc_projeto')];
 echo '<div class="wrap fgc-dashboard"><h1>FUNDART — '.esc_html($filter==='arte-para-todos'?'Arte para Todos':($filter==='outros'?'Outros Projetos':'Gestão Cultural')).'</h1>';
 echo '<p>Ambiente de testes. Não utilize dados pessoais reais até autorização e validação da proteção de dados.</p>';
 echo '<div class="fgc-stats">';
 foreach($items as $label=>$count)echo '<section class="fgc-stat"><strong>'.number_format_i18n($count).'</strong><span>'.esc_html($label).'</span></section>';
 echo '</div><div class="fgc-links">';
 foreach(fgc_types() as $type=>$cfg)echo '<a class="button button-primary" href="'.esc_url(admin_url('edit.php?post_type='.$type)).'">Administrar '.esc_html($cfg[0]).'</a> ';
 echo '<a class="button" href="'.esc_url(admin_url('admin.php?page=fundart-inscricoes-gestao')).'">Inscrições e impressão</a>';
 echo '</div><h2>Situação de turmas</h2><div class="fgc-table"><table class="widefat striped"><thead><tr><th>Turma</th><th>Curso</th><th>Professor</th><th>Vagas</th><th>Horário</th><th>Local</th><th>Situação</th></tr></thead><tbody>';
 $q=new WP_Query(['post_type'=>'fgc_turma','post_status'=>'publish','posts_per_page'=>100,'no_found_rows'=>true]);
 if(!$q->posts)echo '<tr><td colspan="7">Nenhuma turma cadastrada.</td></tr>';
 foreach($q->posts as $post){
  echo '<tr><td><a href="'.esc_url(get_edit_post_link($post->ID)).'">'.esc_html($post->post_title).'</a></td>';
  foreach(['curso','professor','vagas','horario','local','situacao'] as $key)echo '<td>'.esc_html(get_post_meta($post->ID,'_fgc_'.$key,true)).'</td>';
  echo '</tr>';
 }
 echo '</tbody></table></div><p class="description">Indicadores exibem registros cadastrados nesta instalação. Ocupação só estará disponível após integração segura das matrículas.</p></div>';
}
function fgc_fields(): array {
 return [
  'fgc_curso'=>['modalidade'=>'Modalidade','projeto'=>'Projeto (Arte para Todos ou outro)','situacao'=>'Situação','carga_horaria'=>'Carga horária','idade_minima'=>'Idade mínima','idade_maxima'=>'Idade máxima','informacoes'=>'Informações e pré-requisitos'],
  'fgc_turma'=>['curso'=>'Curso','projeto'=>'Projeto','professor'=>'Professor','vagas'=>'Quantidade de vagas','inscritos'=>'Quantidade de alunos (somente total não identificável)','horario'=>'Dias e horários','local'=>'Local / polo','inicio'=>'Data de início','fim'=>'Data de término','situacao'=>'Situação'],
  'fgc_professor'=>['area'=>'Área de atuação','vinculo'=>'Tipo de vínculo','situacao'=>'Situação','disponibilidade'=>'Disponibilidade','observacoes'=>'Observações sem dados pessoais sensíveis'],
  'fgc_projeto'=>['categoria'=>'Categoria','situacao'=>'Situação','inicio'=>'Início','fim'=>'Término','coordenacao'=>'Coordenação','informacoes'=>'Informações institucionais'],
 ];
}
add_action('add_meta_boxes',function(){
 foreach(fgc_fields() as $type=>$fields)add_meta_box('fgc_campos','Configuração e informações','fgc_metabox',$type,'normal','high');
});
function fgc_metabox(WP_Post $post):void {
 wp_nonce_field('fgc_save_'.$post->ID,'fgc_nonce');
 foreach(fgc_fields()[$post->post_type]??[] as $key=>$label){
  $value=(string)get_post_meta($post->ID,'_fgc_'.$key,true);
  echo '<p><label for="fgc_'.esc_attr($key).'"><strong>'.esc_html($label).'</strong></label><br>';
  if($key==='situacao') {
   echo '<select name="fgc['.esc_attr($key).']" id="fgc_'.esc_attr($key).'">';
   foreach(['ativo'=>'Ativo','inativo'=>'Inativo','planejamento'=>'Em planejamento','encerrado'=>'Encerrado'] as $v=>$l)
    echo '<option value="'.esc_attr($v).'" '.selected($value,$v,false).'>'.esc_html($l).'</option>';
   echo '</select>';
  }elseif(in_array($key,['inicio','fim'],true))
   echo '<input type="date" id="fgc_'.esc_attr($key).'" name="fgc['.esc_attr($key).']" value="'.esc_attr($value).'">';
  elseif(in_array($key,['vagas','inscritos','idade_minima','idade_maxima'],true))
   echo '<input type="number" min="0" max="999999" id="fgc_'.esc_attr($key).'" name="fgc['.esc_attr($key).']" value="'.esc_attr($value).'">';
  else echo '<input class="widefat" id="fgc_'.esc_attr($key).'" maxlength="500" name="fgc['.esc_attr($key).']" value="'.esc_attr($value).'">';
  echo '</p>';
 }
}
add_action('save_post',function($id,$post){
 if(!isset(fgc_types()[$post->post_type]) || wp_is_post_revision($id))return;
 if(!isset($_POST['fgc_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['fgc_nonce'])),'fgc_save_'.$id))return;
 if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)return;
 if(!current_user_can('edit_post',$id))return;
 $values=isset($_POST['fgc'])&&is_array($_POST['fgc'])?wp_unslash($_POST['fgc']):[];
 foreach(fgc_fields()[$post->post_type] as $key=>$label){
  $value=sanitize_text_field($values[$key]??'');
  if(in_array($key,['vagas','inscritos','idade_minima','idade_maxima'],true))$value=(string)max(0,(int)$value);
  update_post_meta($id,'_fgc_'.$key,$value);
 }
},10,2);
function fgc_inscricoes():void {
 if(!current_user_can('edit_fgc_items'))wp_die('Acesso não autorizado.');
 echo '<div class="wrap"><h1>Inscrições de homologação — FUNDART</h1><p>Área restrita. Dados reais de alunos não devem ser coletados neste ambiente.</p>';
 echo '<p><button class="button" type="button" onclick="window.print()">Imprimir relação</button></p><table class="widefat striped"><thead><tr><th>Registro</th><th>Tipo</th><th>Oficina</th><th>Data</th></tr></thead><tbody>';
 $q=new WP_Query(['post_type'=>'fundart_inscricao','post_status'=>'private','posts_per_page'=>100,'no_found_rows'=>true]);
 if(!$q->posts)echo '<tr><td colspan="4">Nenhuma inscrição de teste.</td></tr>';
 foreach($q->posts as $p){
  echo '<tr><td>'.esc_html($p->ID).'</td><td>'.esc_html(get_post_meta($p->ID,'_fundart_tipo',true)).'</td><td>'.esc_html(get_post_meta($p->ID,'_fundart_oficina',true)).'</td><td>'.esc_html(get_the_date('d/m/Y H:i',$p)).'</td></tr>';
 }
 echo '</tbody></table><p>Impressão apresenta dados resumidos, sem CPF, RG, endereço ou dados de menores.</p></div>';
}
add_action('admin_enqueue_scripts',function($hook){
 if(!current_user_can('edit_fgc_items'))return;
 wp_register_style('fgc-admin',false,[], '0.1.0');wp_enqueue_style('fgc-admin');
 wp_add_inline_style('fgc-admin','.fgc-dashboard{max-width:1360px}.fgc-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:15px;margin:24px 0}.fgc-stat{background:#fff;border-radius:13px;padding:25px;border:1px solid #d8e3ec;box-shadow:0 3px 12px #102b4212}.fgc-stat strong{font-size:32px;color:#074c80;display:block}.fgc-stat span{font-size:14px;font-weight:700}.fgc-links{display:flex;gap:9px;flex-wrap:wrap;margin:20px 0}.fgc-table{overflow-x:auto}');
});
