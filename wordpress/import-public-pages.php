<?php
/**
 * FUNDART: Importador incremental de paginas publicas verificadas.
 * Usa DOMXPath sobre .page_content; preserva slugs; nunca sobrescreve paginas existentes.
 * Rodar somente em ambiente de teste, via WP-CLI.
 */
if(!defined('ABSPATH')||!defined('WP_CLI'))exit(1);
$manifest='/manifest.json';
if(!is_readable($manifest))WP_CLI::error('Inventario ausente');
$inventory=json_decode(file_get_contents($manifest),true);
if(!is_array($inventory))WP_CLI::error('Inventario invalido');
$limit=35;$count=0;$skipped=0;$failed=0;
$excluded=['carousel-slider','navegador-de-arquivos','galeria'];
foreach($inventory['urls']??[] as $entry){
 if($count>=$limit)break;
 $source=(string)($entry['url']??'');
 $path=(string)($entry['path']??'');
 $map=(string)($entry['sitemap']??'');
 if(!str_contains($map,'page-sitemap')||!preg_match('~^/[a-z0-9][a-z0-9/-]*/$~i',$path))continue;
 $slug=trim($path,'/');
 if(in_array($slug,$excluded,true)){$skipped++;continue;}
 if(str_contains($slug,'/'))continue; // hierarchical URL requires separately verified parents
 if(get_page_by_path($slug,OBJECT,'page')){$skipped++;continue;}
 $response=wp_remote_get($source,['timeout'=>16,'redirection'=>2,'user-agent'=>'FUNDART-TestMigration/1.0']);
 if(is_wp_error($response)||wp_remote_retrieve_response_code($response)!==200){$failed++;continue;}
 $raw=wp_remote_retrieve_body($response);
 if(strlen($raw)>1800000||strlen($raw)<400){$failed++;continue;}
 $dom=new DOMDocument('1.0','UTF-8');
 libxml_use_internal_errors(true);
 $loaded=$dom->loadHTML('<?xml encoding="utf-8" ?>'.$raw,LIBXML_NONET|LIBXML_NOWARNING|LIBXML_NOERROR);
 libxml_clear_errors();
 if(!$loaded){$failed++;continue;}
 $xp=new DOMXPath($dom);
 $nodes=$xp->query("//*[contains(concat(' ',normalize-space(@class),' '),' page_content ')]");
 $h1=$xp->query('//h1[contains(concat(" ",normalize-space(@class)," ")," page_title ")]');
 if(!$nodes||!$nodes->length||!$h1||!$h1->length){$failed++;continue;}
 $container=$nodes->item(0);
 $title=trim($h1->item(0)->textContent);
 if(mb_strlen($title)<4||mb_strlen($title)>220){$failed++;continue;}
 $html='';
 foreach($container->childNodes as $child)$html.=$dom->saveHTML($child);
 $html=wp_kses_post($html);
 if(mb_strlen(wp_strip_all_tags($html))<70){$failed++;continue;}
 // Convert ONLY exact internal links to the test domain; other links untouched.
 // Keep original links until their destination is verified as migrated; avoids dead local URLs.
 $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>$title,'post_name'=>$slug,'post_content'=>$html],true);
 if(is_wp_error($id)){$failed++;continue;}
 update_post_meta($id,'_fundart_original_url',esc_url_raw($source));
 update_post_meta($id,'_fundart_migration_status','importado_html_revisao_pendente');
 $count++;
 WP_CLI::log('IMPORTED '.$slug.' '.$id);
}
WP_CLI::success('NEW_PAGES='.$count.' EXISTING_SKIPPED='.$skipped.' NOT_IMPORTED='.$failed);
