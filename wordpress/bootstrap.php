<?php
/**
 * Inicialização idempotente do portal FUNDART para homologação.
 * Execute somente via WP-CLI após a ativação do tema.
 */
if (!defined('ABSPATH')) { exit(1); }
$pages = [
  'a-fundart' => ['A FundArt', '<p>A Fundação de Arte e Cultura de Ubatuba promove o acesso à cultura, a valorização dos artistas e as manifestações culturais do município.</p><p>Conheça as ações, os equipamentos culturais, os editais e os canais institucionais da FUNDART.</p>'],
  'agenda' => ['Agenda Cultural', '<p>Acompanhe a programação cultural oficial da FUNDART. As atividades e datas serão publicadas e atualizadas pela equipe responsável.</p><p><a href="/eventos/">Consultar eventos culturais cadastrados</a></p>'],
  'oficinas' => ['Oficinas Culturais', '<p>Confira oficinas culturais, modalidades disponíveis e inscrições publicadas pela FUNDART.</p><p>Os editais e critérios de seleção devem ser consultados antes de qualquer inscrição.</p>'],
  'editais' => ['Editais e Chamamentos', '<p>Espaço para editais de fomento, credenciamento, Cultura Viva, eventos, concursos, licitações e convocações institucionais.</p><p><a href="/arte-para-todos-2026/">Arte para Todos 2026 — orientações sobre credenciamento e inscrição</a></p>'],
  'projetos-acoes' => ['Projetos e Ações', '<p>Conheça iniciativas e atividades culturais realizadas ou apoiadas pela FUNDART.</p>'],
  'conselhos' => ['Conselhos Municipais', '<p>Espaço para os conselhos vinculados às políticas culturais de Ubatuba, com informações institucionais e publicações oficiais.</p>'],
  'transparencia' => ['Transparência', '<p>Acesse publicações e informações de transparência relacionadas à Fundação de Arte e Cultura de Ubatuba.</p><p>Os pedidos de acesso à informação relativos à FUNDART podem ser formalizados pelo <a href="https://informabr.cgu.gov.br/">SIC / Fala.BR</a>.</p>'],
  'ouvidoria' => ['Ouvidoria Setorial da FUNDART', '<p>Canal de atendimento setorial, restrito a manifestações e solicitações relacionadas à Fundação de Arte e Cultura de Ubatuba.</p>'],
  'contato' => ['Contato', '<p>FUNDART — Fundação de Arte e Cultura de Ubatuba</p><p>Praça Nóbrega, 54 — Centro, Ubatuba/SP.</p>'],
  'acessibilidade' => ['Acessibilidade', '<p>O portal conta com recursos para ampliar ou reduzir o tamanho do texto e modo de alto contraste. Utilize o menu de acessibilidade disponível no cabeçalho.</p>'],
  'privacidade' => ['Privacidade', '<p>As informações pessoais fornecidas nos canais institucionais devem ser tratadas segundo a legislação de proteção de dados. Esta página deverá ser complementada pela política oficial aprovada pela FUNDART.</p>'],
  'mapa-do-site' => ['Mapa do Site', '<p>Navegue pelo menu principal para conhecer os serviços, publicações e canais institucionais da FUNDART.</p>'],
  'arte-para-todos-2026' => ['Arte para Todos 2026', '<h2>Inscrições e credenciamento</h2><p>O Credenciamento nº 03/2025 prevê apresentação presencial dos documentos conforme o edital. A implementação de inscrição online depende de autorização formal da FUNDART.</p><p><a href="https://fundart.com.br/edital/edital-no-62-2025-credenciamento-no-03-2025-projeto-de-oficinas-culturais-arte-para-todos-2026/">Consultar edital oficial</a></p><h2>Migração do site</h2><p>O novo portal WordPress está em homologação. A migração definitiva será realizada após testes e preservação do site de apresentação.</p>'],
  'pnab' => ['PNAB', '<p>Informações sobre a Política Nacional Aldir Blanc e publicações da FUNDART.</p>'],
  'lei-paulo-gustavo' => ['Lei Paulo Gustavo', '<p>Informações sobre ações e editais culturais vinculados à Lei Paulo Gustavo.</p>'],
  'equipamentos-culturais' => ['Equipamentos Culturais', '<p>Conheça os espaços e equipamentos de cultura do município de Ubatuba.</p>'],
  'patrimonio-cultural' => ['Patrimônio Cultural', '<p>Informações sobre o patrimônio cultural de Ubatuba.</p>'],
  'historia-de-ubatuba' => ['História de Ubatuba', '<p>História e memória cultural de Ubatuba.</p>'],
  'cultura-caicara' => ['Cultura Caiçara', '<p>Espaço de valorização da cultura caiçara.</p>'],
  'cultura-indigena' => ['Cultura Indígena', '<p>Espaço de valorização das manifestações culturais indígenas.</p>'],
  'cultura-quilombola' => ['Cultura Quilombola', '<p>Espaço de valorização das manifestações culturais quilombolas.</p>'],
];
$ids = [];
foreach ($pages as $slug => [$title, $content]) {
  $page = get_page_by_path($slug, OBJECT, 'page');
  if (!$page) {
    $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_name'=>$slug,'post_title'=>$title,'post_content'=>$content],true);
    if (is_wp_error($id)) { WP_CLI::warning($slug.': '.$id->get_error_message()); continue; }
  } else { $id=$page->ID; }
  $ids[$slug]=$id;
}
$existing_menu=wp_get_nav_menu_object('Menu principal FUNDART');
$menu_id=$existing_menu ? (int)$existing_menu->term_id : wp_create_nav_menu('Menu principal FUNDART');
if (!is_wp_error($menu_id) && $menu_id) {
  $current=wp_get_nav_menu_items($menu_id) ?: [];
  $mapped=array_map(static fn($i)=>(int)$i->object_id,$current);
  foreach (['a-fundart'=>'A FundArt','agenda'=>'Agenda','oficinas'=>'Oficinas','editais'=>'Editais','projetos-acoes'=>'Projetos e Ações','conselhos'=>'Conselhos','transparencia'=>'Transparência','ouvidoria'=>'Ouvidoria Setorial'] as $slug=>$label) {
    $id=$ids[$slug]??0; if (!$id || in_array((int)$id,$mapped,true)) continue;
    wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>$label,'menu-item-object'=>'page','menu-item-object-id'=>$id,'menu-item-type'=>'post_type','menu-item-status'=>'publish']);
  }
  $locations=get_theme_mod('nav_menu_locations',[]);
  $locations['principal']=(int)$menu_id;
  set_theme_mod('nav_menu_locations',$locations);
}
update_option('timezone_string','America/Sao_Paulo');
update_option('permalink_structure','/%postname%/');
flush_rewrite_rules();
WP_CLI::success('FUNDART_SETUP_OK; PAGES='.count($ids));
