<?php
/** FUNDART non-mutating technical inventory; prints counts only (no personal records). */
if (!defined('ABSPATH') || !defined('WP_CLI')) exit(1);
global $wpdb,$wp_version;
$counts=[];
foreach (['page','post','attachment','fundart_edital','fundart_evento','fundart_oficina','fundart_conselho','fundart_inscricao','fgc_curso','fgc_turma','fgc_professor','fgc_projeto'] as $type) {
 $obj=wp_count_posts($type);
 $counts[$type]=['published'=>(int)($obj->publish??0),'private'=>(int)($obj->private??0),'draft'=>(int)($obj->draft??0),'inherit'=>(int)($obj->inherit??0)];
}
$migration=$wpdb->get_results("SELECT p.post_type AS type,p.post_status AS status,COUNT(*) AS total
 FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON pm.post_id=p.ID
 WHERE pm.meta_key='_fundart_original_url' GROUP BY p.post_type,p.post_status",ARRAY_A);
$refs=$wpdb->get_row("SELECT
  SUM(CASE WHEN post_content LIKE '%fundart.com.br/wp-content/uploads/%' THEN 1 ELSE 0 END) AS still_refs_source_media,
  SUM(CASE WHEN post_content LIKE '%fundart.com.br/%' THEN 1 ELSE 0 END) AS still_refs_source_site,
  SUM(CASE WHEN post_content LIKE '%[fundart_%' THEN 1 ELSE 0 END) AS shortcode_occurrences
  FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('post','page')",ARRAY_A);
$source_duplicates=$wpdb->get_var("SELECT COUNT(*) FROM (
 SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key='_fundart_original_url'
 GROUP BY meta_value HAVING COUNT(*)>1
) s");
$dates=$wpdb->get_results("SELECT YEAR(post_date) AS y,COUNT(*) AS n
 FROM {$wpdb->posts} WHERE post_type='post' AND post_status='publish' GROUP BY YEAR(post_date) ORDER BY y DESC LIMIT 15",ARRAY_A);
$missing_content=$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} p WHERE p.post_type='post' AND p.post_status='publish' AND CHAR_LENGTH(TRIM(p.post_content)) < 100");
$page=$wpdb->get_results("SELECT ID,post_title FROM {$wpdb->posts} WHERE post_type='page' AND post_status='publish' AND post_name IN ('acervo','noticias','editais','pnab','transparencia','ouvidoria')",ARRAY_A);
$menus=wp_get_nav_menus();
$menu_counts=[];foreach($menus as $m)$menu_counts[]=['items'=>count(wp_get_nav_menu_items($m->term_id)?:[])];
$plugins=get_option('active_plugins',[]);
$role_counts=[];foreach(wp_roles()->roles as $name=>$item){$c=count_users();$role_counts[$name]=(int)($c['avail_roles'][$name]??0);}
$parentless=$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} p WHERE p.post_type='page' AND p.post_status='publish' AND p.post_parent>0 AND NOT EXISTS(SELECT 1 FROM {$wpdb->posts} par WHERE par.ID=p.post_parent AND par.post_status='publish')");
$out=[
 'core_version'=>$wp_version,
 'php_version'=>PHP_VERSION,
 'theme'=>wp_get_theme()->get('Name'),
 'theme_version'=>wp_get_theme()->get('Version'),
 'active_plugins'=>$plugins,
 'public_post_types'=>$counts,
 'migrated_meta_counts'=>$migration,
 'source_url_duplicate_groups'=>(int)$source_duplicates,
 'published_content_still_linking_to_source'=>$refs,
 'news_year_distribution'=>$dates,
 'published_news_shorter_than_100_chars'=>(int)$missing_content,
 'key_pages_present'=>array_map(fn($p)=>$p['post_title'],$page),
 'nav_menu_count'=>count($menus),
 'nav_menu_sizes'=>$menu_counts,
 'stale_page_parent_links'=>(int)$parentless,
 'settings'=>[
  'permalink_structure'=>get_option('permalink_structure'),
  'site_visibility'=>get_option('blog_public'),
  'anyone_can_register'=>get_option('users_can_register'),
  'timezone'=>get_option('timezone_string'),
  'wp_debug'=>defined('WP_DEBUG')?WP_DEBUG:null,
  'force_ssl_admin'=>defined('FORCE_SSL_ADMIN')?FORCE_SSL_ADMIN:null,
  'disallow_file_edit'=>defined('DISALLOW_FILE_EDIT')?DISALLOW_FILE_EDIT:null,
  'full_import_cursor'=>(int)get_option('fundart_full_import_cursor',0),
 ],
 'user_role_counts'=>$role_counts,
 'cron_scheduled_count'=>is_array(get_option('cron'))?count(get_option('cron')):0
];
WP_CLI::log('FUNDART_AUDIT_JSON='.wp_json_encode($out,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
