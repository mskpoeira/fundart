<?php
/** Incremental public-content migration. WP-CLI only. Never modifies existing records. */
if (!defined('WP_CLI') || !defined('ABSPATH')) exit(1);
$inv=json_decode(file_get_contents('/manifest.json'),true);
if (!is_array($inv)||!isset($inv['urls'])) WP_CLI::error('Invalid sitemap inventory');
$records=[];
foreach($inv['urls'] as $entry){
 $url=(string)($entry['url']??'');$path=(string)($entry['path']??'');$map=(string)($entry['sitemap']??'');
 if(!preg_match('~^https?://(www\\.)?fundart\\.com\\.br/~i',$url))continue;
 if(!preg_match('~^/[a-z0-9][a-z0-9/_-]*/$~i',$path))continue;
 if(str_contains($path,'/wp-content/')||str_contains($path,'/feed/'))continue;
 $type=str_contains($map,'post-sitemap')?'post':(str_contains($map,'page-sitemap')?'page':null);
 if(!$type)continue;
 $records[$type.'|'.$path]=['url'=>$url,'path'=>$path,'type'=>$type];
}
$records=array_values($records);$size=count($records);
if(!$size) WP_CLI::error('No valid sources');
$cursor=(int)get_option('fundart_full_import_cursor',0);
$limit=max(1,min(120,(int)(getenv('FUNDART_BATCH_LIMIT')?:25)));
$visited=0;$added=0;$existing=0;$deferred=0;$errors=0;$error_samples=[];
while($visited<$size && $added<$limit){
 $r=$records[($cursor+$visited)%$size];$visited++;
 $path=trim($r['path'],'/');$segments=explode('/',$path);$slug=end($segments);
 if(in_array($slug,['carousel-slider','navegador-de-arquivos'],true))continue;
 $prior=get_page_by_path($path,OBJECT,$r['type']);
 if($prior){$existing++;continue;}
 $parent=0;
 if($r['type']==='page' && count($segments)>1){
  $parents=$segments;array_pop($parents);
  $record=get_page_by_path(implode('/',$parents),OBJECT,'page');
  if(!$record){$deferred++;continue;}
  $parent=(int)$record->ID;
 }
 $response=wp_remote_get($r['url'],['timeout'=>14,'redirection'=>3,'user-agent'=>'FUNDART-StagingMigration/2.0']);
 if(is_wp_error($response)||wp_remote_retrieve_response_code($response)!==200){$errors++;if(count($error_samples)<20)$error_samples[]=$path.':fetch';continue;}
 $raw=wp_remote_retrieve_body($response);
 if(strlen($raw)<450||strlen($raw)>2500000){$errors++;continue;}
 $doc=new DOMDocument('1.0','UTF-8');libxml_use_internal_errors(true);
 $ok=$doc->loadHTML('<?xml encoding="utf-8" ?>'.$raw,LIBXML_NONET|LIBXML_NOWARNING|LIBXML_NOERROR);
 libxml_clear_errors();
 if(!$ok){$errors++;continue;}
 $xp=new DOMXPath($doc);
 $titles=$xp->query('//h1[contains(concat(" ",normalize-space(@class)," ")," page_title ")]|//h1[contains(concat(" ",normalize-space(@class)," ")," post_title ")]');
 $containers=$xp->query("//*[contains(concat(' ',normalize-space(@class),' '),' page_content ') or contains(concat(' ',normalize-space(@class),' '),' post_content ') or contains(concat(' ',normalize-space(@class),' '),' entry-content ') or contains(concat(' ',normalize-space(@class),' '),' single-content ')]");
 if(!$containers||!$containers->length||!$titles||!$titles->length){$errors++;if(count($error_samples)<20)$error_samples[]=$path.':selector';continue;}
 $title=trim($titles->item(0)->textContent);
 if(mb_strlen($title)<3||mb_strlen($title)>220){$errors++;continue;}
 $node=$containers->item(0);$html='';
 foreach($node->childNodes as $child)$html.=$doc->saveHTML($child);
 $html=wp_kses_post($html);
 if(mb_strlen(wp_strip_all_tags($html))<45){$errors++;continue;}
 $date='';
 if(preg_match('~<meta[^>]+property=["\\x27]article:published_time["\\x27][^>]+content=["\\x27]([^"\\x27]+)~i',$raw,$match))
  $date=gmdate('Y-m-d H:i:s',strtotime($match[1]));
 $args=['post_type'=>$r['type'],'post_status'=>'publish','post_name'=>$slug,'post_title'=>$title,'post_content'=>$html];
 if($r['type']==='page')$args['post_parent']=$parent;
 if($date&&$r['type']==='post')$args['post_date']=$date;
 $id=wp_insert_post($args,true);
 if(is_wp_error($id)){$errors++;continue;}
 update_post_meta($id,'_fundart_original_url',esc_url_raw($r['url']));
 update_post_meta($id,'_fundart_migration_status','importado_html_revisao_pendente');
 $added++;WP_CLI::log('IMPORTED '. $r['type'].' '.$path.' ID='.$id);
}
$next=($cursor+$visited)%$size;
update_option('fundart_full_import_cursor',$next,false);
WP_CLI::success('IMPORT_METRICS '.wp_json_encode(['added'=>$added,'existing'=>$existing,'deferred'=>$deferred,'failed'=>$errors,'visited'=>$visited,'eligible'=>$size,'cursor'=>$next,'errors_sample'=>$error_samples],JSON_UNESCAPED_UNICODE));
