<?php
declare(strict_types=1);
const AUTH_DIR='/home/matanadmin/.panda-bc-admin';
const SITE_CONFIG='/home/matanadmin/web/bc.panda-il.com/public_html/config.js';
function start_admin():void {
 ini_set('session.use_strict_mode','1');ini_set('session.use_only_cookies','1');
 session_name('PandaBCAdmin');
 session_set_cookie_params(['lifetime'=>0,'path'=>'/admin/','secure'=>true,'httponly'=>true,'samesite'=>'Strict']);
 session_start();
 header('Cache-Control: no-store, private');header('X-Content-Type-Options: nosniff');
 header('X-Frame-Options: DENY');header('Referrer-Policy: no-referrer');
 header("Content-Security-Policy: frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
}
function token():string {if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
function reject(int $status,string $message):never {http_response_code($status);header('Content-Type: application/json; charset=utf-8');echo json_encode(['error'=>$message]);exit;}
function authenticated():void {if(empty($_SESSION['authenticated']) || time()-(int)($_SESSION['last']??0)>1800){$_SESSION=[];reject(401,'authentication_required');}$_SESSION['last']=time();}
function validate_data(array $d):void {
 foreach(['contact','terminal','marketing'] as $k)if(!isset($d[$k])||!is_array($d[$k]))reject(422,'missing_'.$k);
 foreach(['phone','email','whatsapp','github'] as $k)if(!is_string($d['contact'][$k]??null))reject(422,'invalid_contact_'.$k);
 foreach(['user','bio','skills'] as $k)if(!is_string($d['terminal'][$k]??null))reject(422,'invalid_terminal_'.$k);
 if(!is_array($d['terminal']['commands']??null)||count($d['terminal']['commands'])>100)reject(422,'commands');
 foreach($d['terminal']['commands'] as $c){if(!is_array($c))reject(422,'command');foreach(['trigger','output','url','btn'] as $k)if(!is_string($c[$k]??null))reject(422,'command_'.$k);}
 foreach(['he','en','es'] as $l){$m=$d['marketing'][$l]??null;
 if(!is_array($m)||!is_array($m['services']??null)||count($m['services'])>80)reject(422,'invalid_'.$l);
 foreach(['title','badge','bio'] as $k)if(!is_string($m[$k]??null))reject(422,'invalid_'.$l.'_'.$k);
 foreach($m['services'] as $s){if(!is_array($s))reject(422,'service');foreach(['icon','title','desc','btn'] as $k)if(!is_string($s[$k]??null))reject(422,'service_'.$k);if(!is_array($s['features']??null))reject(422,'features');foreach($s['features'] as $f)if(!is_string($f))reject(422,'feature');}
 }
}
