<?php
/* Importação inicial, idempotente, de páginas institucionais públicas FUNDART.
 * Fonte consultada: https://fundart.com.br/a-fundart/ e /objetivos/.
 * Execute via WP-CLI apenas no site de homologação.
 */
if (!defined('ABSPATH') || !defined('WP_CLI')) { exit(1); }
$items=[
 'a-fundart'=>[
 'O que é a FundArt',
 '<p>A Fundação de Arte e Cultura de Ubatuba (FundArt) atua na execução da política cultural municipal, promovendo programas, projetos, atividades formativas e manifestações artístico-culturais em Ubatuba.</p><p>Instituída pela Lei Municipal nº 893, de 25 de novembro de 1987, desenvolve ações de valorização da produção artística e de preservação da memória e do patrimônio cultural do município.</p><h2>Atuação</h2><p>Entre suas atividades estão exposições, apresentações artísticas, concursos, oficinas, cursos, seminários e atividades de formação cultural.</p><p><a href="/objetivos/">Conheça os objetivos institucionais</a> e <a href="/equipe/">consulte as informações da equipe</a>.</p>',
 'https://fundart.com.br/a-fundart/'
 ],
 'objetivos'=>[
 'Objetivos da FundArt',
 '<p>A FundArt tem entre seus objetivos formular e executar ações de política cultural em Ubatuba, ampliar o acesso aos bens culturais, preservar o patrimônio artístico, histórico e cultural e incentivar o desenvolvimento de atividades artísticas.</p><h2>Principais áreas de atuação</h2><ul><li>Articulação com entidades públicas e privadas para realizar programas culturais.</li><li>Estímulo à participação da comunidade nas políticas culturais.</li><li>Apoio à formação de grupos artísticos e a instituições culturais.</li><li>Preservação da memória municipal e manutenção de espaços culturais.</li><li>Publicações, exposições, espetáculos, cursos, debates e intercâmbio cultural.</li></ul><p>O texto completo dos objetivos consta da <a href="https://fundart.com.br/objetivos/">página institucional publicada pela Fundação</a>.</p>',
 'https://fundart.com.br/objetivos/'
 ],
 'equipe'=>[
 'Equipe e organização',
 '<p>Esta seção apresenta a estrutura administrativa e os órgãos colegiados da Fundação de Arte e Cultura de Ubatuba.</p><p>A composição nominal de dirigentes e conselhos está sujeita a alterações; durante a conferência dos dados para migração, consulte também a <a href="https://fundart.com.br/equipe/">relação pública atual da equipe</a>.</p>',
 'https://fundart.com.br/equipe/'
 ],
 'noticias'=>[
 'Notícias',
 '<p>Acompanhe as notícias institucionais publicadas no portal FUNDART.</p><p>O histórico de publicações ainda está em processo de migração e conferência. Enquanto isso, o <a href="https://fundart.com.br/">acervo original</a> permanece disponível para consulta.</p>',
 'https://fundart.com.br/'
 ],
];
$count=0;
foreach($items as $slug=>$row){
 $p=get_page_by_path($slug,OBJECT,'page');
 $data=['post_type'=>'page','post_status'=>'publish','post_name'=>$slug,'post_title'=>$row[0],'post_content'=>$row[1]];
 if($p){$data['ID']=$p->ID;$id=wp_update_post($data,true);} else {$id=wp_insert_post($data,true);}
 if(is_wp_error($id)){WP_CLI::warning($slug.': '.$id->get_error_message());continue;}
 update_post_meta($id,'_fundart_original_url',$row[2]);
 update_post_meta($id,'_fundart_migration_status','resumo_institucional_revisar');
 $count++;
}
WP_CLI::success('INSTITUTIONAL_PAGES_PREPARED='.$count);
