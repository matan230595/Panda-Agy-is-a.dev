<?php
declare(strict_types=1);
require __DIR__.'/common.php';start_admin();authenticated();
header('Content-Type: application/json; charset=utf-8');
if($_SERVER['REQUEST_METHOD']==='GET'){
 $old=@file_get_contents(SITE_CONFIG);if($old===false)reject(503,'config_unavailable');
 echo json_encode(['revision'=>hash('sha256',$old),'csrf'=>token()]);exit;
}
if($_SERVER['REQUEST_METHOD']!=='POST')reject(405,'method');
if(!hash_equals(token(),(string)($_SERVER['HTTP_X_CSRF_TOKEN']??'')))reject(403,'csrf');
$raw=file_get_contents('php://input');if($raw===false||strlen($raw)>512000)reject(413,'payload_size');
try{$body=json_decode($raw,true,64,JSON_THROW_ON_ERROR);}catch(Throwable $e){reject(400,'json');}
if(!is_array($body)||!is_string($body['revision']??null)||!is_array($body['data']??null))reject(400,'payload');
validate_data($body['data']);
$next='window.SITE_DATA = '.json_encode($body['data'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_THROW_ON_ERROR).";\n";
if(strlen($next)>512000)reject(413,'too_large');
$lock=fopen(AUTH_DIR.'/config.lock','c');if(!$lock||!flock($lock,LOCK_EX))reject(503,'lock');
$old=@file_get_contents(SITE_CONFIG);if($old===false)reject(503,'read');
if(!hash_equals(hash('sha256',$old),$body['revision']))reject(409,'version_conflict');
$dir=AUTH_DIR.'/backups';if(!is_dir($dir)&&!mkdir($dir,0700,true))reject(503,'backup_dir');
$backup=$dir.'/config-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.js';
if(file_put_contents($backup,$old,LOCK_EX)===false)reject(503,'backup');chmod($backup,0600);
$tmp=tempnam(dirname(SITE_CONFIG),'.panda-');if($tmp===false)reject(503,'temp');
if(file_put_contents($tmp,$next,LOCK_EX)===false){@unlink($tmp);reject(503,'write');}
chmod($tmp,0644);
if(!rename($tmp,SITE_CONFIG)){@unlink($tmp);reject(503,'rename');}
echo json_encode(['saved'=>true,'revision'=>hash('sha256',$next)]);
