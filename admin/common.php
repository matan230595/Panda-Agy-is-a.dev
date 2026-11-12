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
function bounded_string(mixed $v, string $field, int $limit=4000):string {
 if(!is_string($v)||strlen($v)>$limit) reject(422,'invalid_'.$field);
 return $v;
}
function http_url(string $v, string $field, bool $allowEmpty=false):void {
 if($allowEmpty && ($v===''||$v==='#')) return;
 $parts=parse_url($v);
 if($parts===false || !in_array(strtolower((string)($parts['scheme']??'')),['http','https'],true) || empty($parts['host'])) reject(422,'invalid_'.$field);
}
function validate_data(array $d):void {
 foreach(['contact','terminal','marketing'] as $k)if(!isset($d[$k])||!is_array($d[$k]))reject(422,'missing_'.$k);
 foreach(['phone','email','whatsapp','github'] as $k)bounded_string($d['contact'][$k]??null,'contact_'.$k,512);
 http_url($d['contact']['github'],'contact_github',true);
 foreach(['user','bio','skills'] as $k)bounded_string($d['terminal'][$k]??null,'terminal_'.$k,16000);
 if(!is_array($d['terminal']['commands']??null)||count($d['terminal']['commands'])>100)reject(422,'commands');
 foreach($d['terminal']['commands'] as $c){if(!is_array($c))reject(422,'command');foreach(['trigger','output','url','btn'] as $k)bounded_string($c[$k]??null,'command_'.$k,4000);
 http_url($c['url'],'command_url',true);}
 foreach(['he','en','es'] as $l){$m=$d['marketing'][$l]??null;
 if(!is_array($m)||!is_array($m['services']??null)||count($m['services'])>80)reject(422,'invalid_'.$l);
 foreach(['title','badge','bio'] as $k)bounded_string($m[$k]??null,$l.'_'.$k,16000);
 foreach($m['services'] as $s){if(!is_array($s))reject(422,'service');foreach(['icon','title','desc','btn'] as $k)bounded_string($s[$k]??null,'service_'.$k,4000);if(!is_array($s['features']??null))reject(422,'features');if(count($s['features'])>60)reject(422,'too_many_features');foreach($s['features'] as $f)bounded_string($f,'feature',2000);}
 }
}
