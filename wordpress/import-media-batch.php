<?php
/**
 * FUNDART - deterministic, idempotent and resumable media recovery.
 * Invoked exclusively via WP-CLI on the staging WordPress, never on the official site.
 */
if (!defined('ABSPATH') || !defined('WP_CLI')) { exit(1); }
if (wp_parse_url(home_url(),PHP_URL_HOST)!=='fundart.mskpoeira.com.br') {
 WP_CLI::error('Wrong environment: not FUNDART staging');
}
require_once ABSPATH.'wp-admin/includes/file.php';
require_once ABSPATH.'wp-admin/includes/media.php';
require_once ABSPATH.'wp-admin/includes/image.php';
global $wpdb;
$allowed=['jpg','jpeg','png','gif','webp','avif','pdf','doc','docx','odt','xls','xlsx','ods','ppt','pptx','txt','csv','mp3','wav','ogg','mp4','webm'];
$limit=max(1,min(100,(int)(getenv('FUNDART_MEDIA_LIMIT')?:35)));
$maxBytes=35*1024*1024;
$phase=getenv('FUNDART_MEDIA_PHASE')?:'references';
if (!in_array($phase,['references','inventory'],true)) WP_CLI::error('Invalid phase');
$report=['phase'=>$phase,'limit'=>$limit,'candidate_count'=>0,'attempted'=>0,'created'=>0,'reused'=>0,'updated_posts'=>0,'replaced_occurrences'=>0,'failed'=>0,'skipped'=>0,'failure_samples'=>[],'new_attachment_ids'=>[]];
function fundart_media_normalize($raw,$allowed) {
 $raw=html_entity_decode(trim((string)$raw),ENT_QUOTES|ENT_HTML5,'UTF-8');
 $p=wp_parse_url($raw);
 if (!$p || !in_array(strtolower((string)($p['host']??'')),['fundart.com.br','www.fundart.com.br'],true)) return '';
 if (strtolower((string)($p['scheme']??''))!=='https') return '';
 if (isset($p['user'])||isset($p['pass'])||isset($p['port'])) return '';
 $path=(string)($p['path']??'');
 if (!str_starts_with($path,'/wp-content/uploads/')) return '';
 $file=basename(rawurldecode($path));
 if ($file===''||str_contains($file,'/')||str_contains($file,'\\')) return '';
 $ext=strtolower(pathinfo($file,PATHINFO_EXTENSION));
 if (!in_array($ext,$allowed,true)) return '';
 return 'https://fundart.com.br'.$path;
}
function fundart_media_refs($body,$allowed) {
 preg_match_all('~https://(?:www\\.)?fundart\\.com\\.br/wp-content/uploads/[^\\s"\\x27<>\\)]+~iu',$body,$matches);
 $links=[];
 foreach(($matches[0]??[]) as $raw){
  $raw=rtrim($raw,',;');
  $url=fundart_media_normalize($raw,$allowed);
  if ($url) $links[$url]=true;
 }
 return array_keys($links);
}
function fundart_media_known_id($source){
 global $wpdb;
 return (int)$wpdb->get_var($wpdb->prepare(
  "SELECT pm.post_id FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID=pm.post_id
   WHERE pm.meta_key='_fundart_original_media_url' AND pm.meta_value=%s AND p.post_type='attachment'
   ORDER BY pm.post_id ASC LIMIT 1",$source));
}
function fundart_media_download($source,$maxBytes){
 // External fetch restricted to the public official domain, one hop at a time.
 $tmp=wp_tempnam('fundart-media-');
 if (!$tmp) return new WP_Error('temp','Unable to allocate temporary file');
 $current=$source;$ok=false;$error='';
 for($redirect=0;$redirect<3;$redirect++){
  $req=wp_safe_remote_get($current,['timeout'=>22,'redirection'=>0,'stream'=>true,'filename'=>$tmp,
   'headers'=>['User-Agent'=>'FUNDART-StagingMediaMigration/1.0'],'limit_response_size'=>$maxBytes+1]);
  if(is_wp_error($req)){ $error=$req->get_error_message();break; }
  $code=(int)wp_remote_retrieve_response_code($req);
  if(in_array($code,[301,302,303,307,308],true)){
    $loc=wp_remote_retrieve_header($req,'location');
    $loc=trim((string)$loc);
    $candidate=str_starts_with($loc,'/')?('https://fundart.com.br'.$loc):$loc;
    $next=$candidate&&filter_var($candidate,FILTER_VALIDATE_URL)?$candidate:'';
    if(!$next||!fundart_media_normalize($next,['jpg','jpeg','png','gif','webp','avif','pdf','doc','docx','odt','xls','xlsx','ods','ppt','pptx','txt','csv','mp3','wav','ogg','mp4'])){$error='Redirect to disallowed URL';break;}
    $current=$next;continue;
  }
  if($code!==200){$error='HTTP '.$code;break;}
  $length=filesize($tmp);
  if($length===false||$length<32||$length>$maxBytes){$error='Invalid/oversized file';break;}
  $ok=true;break;
 }
 if(!$ok){@unlink($tmp);return new WP_Error('download',$error?:'Download failed');}
 return $tmp;
}
function fundart_media_import($source,$allowed,$maxBytes){
 $id=fundart_media_known_id($source);
 if($id && get_attached_file($id) && is_file(get_attached_file($id))){
  return ['id'=>$id,'url'=>wp_get_attachment_url($id),'created'=>false];
 }
 $tmp=fundart_media_download($source,$maxBytes);
 if(is_wp_error($tmp))return $tmp;
 $p=wp_parse_url($source);$name=rawurldecode(basename((string)($p['path']??'')));
 $name=sanitize_file_name($name);
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
 $expected=wp_check_filetype($name);
 $ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
 if(!in_array($ext,$allowed,true)||empty($expected['type'])){
  @unlink($tmp);return new WP_Error('type','Unsupported extension');
 }
 if(str_starts_with($expected['type'],'image/') && (!str_starts_with((string)$mime,'image/')||!@getimagesize($tmp))){
  @unlink($tmp);return new WP_Error('mime','Invalid image bytes');
 }
 if($ext==='pdf' && $mime!=='application/pdf'){
  @unlink($tmp);return new WP_Error('mime','Invalid PDF bytes');
 }
 $sha=hash_file('sha256',$tmp);
 // Deduplicate files by their bytes; different source URLs can still resolve to a single attachment.
 global $wpdb;
 $same=(int)$wpdb->get_var($wpdb->prepare("SELECT pm.post_id FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID=pm.post_id WHERE pm.meta_key='_fundart_media_sha256' AND pm.meta_value=%s AND p.post_type='attachment' LIMIT 1",$sha));
 if($same && get_attached_file($same) && is_file(get_attached_file($same))){
  add_post_meta($same,'_fundart_original_media_url',$source,false);
  @unlink($tmp);
  return ['id'=>$same,'url'=>wp_get_attachment_url($same),'created'=>false];
 }
 $sideload=['name'=>$name,'tmp_name'=>$tmp,'size'=>filesize($tmp),'error'=>0];
 $id=media_handle_sideload($sideload,0,null,['post_title'=>pathinfo($name,PATHINFO_FILENAME),'post_status'=>'inherit']);
 if(is_wp_error($id)){@unlink($tmp);return $id;}
 update_post_meta($id,'_fundart_original_media_url',$source);
 update_post_meta($id,'_fundart_media_sha256',$sha);
 update_post_meta($id,'_fundart_media_verified_bytes',(int)filesize(get_attached_file($id)));
 $url=wp_get_attachment_url($id);
 if(!$url||!is_file(get_attached_file($id))||hash_file('sha256',get_attached_file($id))!==$sha){
   wp_delete_attachment($id,true);return new WP_Error('verify','Attachment verification failed');
 }
 return ['id'=>$id,'url'=>$url,'created'=>true];
}
// Collect existing content URLs first; only automatically change published public pages and posts.
$posts=$wpdb->get_results("SELECT ID,post_content FROM {$wpdb->posts} WHERE post_status='publish'
 AND post_type IN ('page','post','fundart_edital','fundart_evento','fundart_oficina') AND post_content LIKE '%fundart.com.br/wp-content/uploads/%'");
$referenced=[];
foreach($posts as $post){
 foreach(fundart_media_refs($post->post_content,$allowed) as $url)$referenced[$url]=true;
}
$urls=array_keys($referenced);sort($urls,SORT_STRING);
if($phase==='inventory'){
 $manifest='/manifest.json';
 if(!is_readable($manifest)) WP_CLI::error('Missing manifest');
 $data=json_decode(file_get_contents($manifest),true);
 if(!is_array($data))WP_CLI::error('Invalid manifest');
 foreach(($data['urls']??[]) as $item){
  $u=fundart_media_normalize((string)($item['url']??''),$allowed);
  if($u)$referenced[$u]=true;
 }
 $urls=array_keys($referenced);sort($urls,SORT_STRING);
}
$report['candidate_count']=count($urls);
$cursorOption='fundart_media_cursor_'.$phase;
$cursor=max(0,(int)get_option($cursorOption,0));
if($cursor>=count($urls))$cursor=0;
$tried=0;$mapping=[];$maxScans=min(count($urls),$limit*3);
while($tried<$maxScans && $report['attempted']<$limit){
 $source=$urls[($cursor+$tried)%count($urls)];$tried++;
 $known=fundart_media_known_id($source);
 if($known && get_attached_file($known) && is_file(get_attached_file($known))){
  $mapping[$source]=wp_get_attachment_url($known);$report['reused']++;continue;
 }
 $report['attempted']++;
 $x=fundart_media_import($source,$allowed,$maxBytes);
 if(is_wp_error($x)){
  $report['failed']++;
  if(count($report['failure_samples'])<15)$report['failure_samples'][]=['source'=>$source,'error'=>$x->get_error_code()];
  continue;
 }
 $mapping[$source]=$x['url'];
 if($x['created']){$report['created']++;$report['new_attachment_ids'][]=$x['id'];}
 else $report['reused']++;
 WP_CLI::log('MEDIA_OK '.basename(wp_parse_url($source,PHP_URL_PATH)).' ID='.$x['id']);
}
if(count($urls)>0)update_option($cursorOption,($cursor+$tried)%count($urls),false);
// Replace references to all *known imported* URLs, even if not part of current batch.
if($phase==='references'){
 foreach($posts as $post){
  $links=fundart_media_refs($post->post_content,$allowed);
  $body=$post->post_content;$before=$body;
  foreach($links as $source){
   $dest=$mapping[$source]??null;
   if(!$dest){$id=fundart_media_known_id($source);if($id && is_file((string)get_attached_file($id)))$dest=wp_get_attachment_url($id);}
   if(!$dest)continue;
   // Rewrite URL literals, both HTML-encoded and plain.
   $sourceWww=str_replace('https://fundart.com.br/','https://www.fundart.com.br/',$source);
   foreach([$source,$sourceWww,htmlspecialchars($source,ENT_QUOTES,'UTF-8'),htmlspecialchars($sourceWww,ENT_QUOTES,'UTF-8')] as $origin){
    if(str_contains($body,$origin)){
     $report['replaced_occurrences']+=substr_count($body,$origin);
     $body=str_replace($origin,esc_url_raw($dest),$body);
    }
   }
  }
  if($body!==$before){
   $updated=wp_update_post(['ID'=>(int)$post->ID,'post_content'=>$body],true);
   if(!is_wp_error($updated))$report['updated_posts']++;
  }
 }
}
$report['remaining_references']=0;
foreach($posts as $post){
 $live=get_post($post->ID);
 if($live && fundart_media_refs($live->post_content,$allowed))$report['remaining_references']++;
}
$report['next_cursor']=count($urls)?($cursor+$tried)%count($urls):0;
WP_CLI::success('FUNDART_MEDIA_METRICS='.wp_json_encode($report,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
