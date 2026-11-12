<?php
declare(strict_types=1);
require __DIR__.'/common.php';start_admin();
if(!empty($_SESSION['authenticated'])) {
 if(time()-(int)($_SESSION['last']??0)<=1800) {
  $_SESSION['last']=time();header('Location: index.php',true,303);exit;
 }
 $_SESSION=[];session_regenerate_id(true);
}
$wrong=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!hash_equals(token(),(string)($_POST['csrf']??''))){http_response_code(403);exit('Invalid request');}
 $ip=(string)($_SERVER['REMOTE_ADDR']??'unknown');
 $rateFile=AUTH_DIR.'/attempts-'.hash('sha256',$ip).'.json';
 $rate=fopen($rateFile,'c+');
 if(!$rate||!flock($rate,LOCK_EX)){http_response_code(503);exit('Login temporarily unavailable');}
 $saved=stream_get_contents($rate);
 $data=json_decode($saved?:'{}',true);
 if(!is_array($data))$data=[];
 $period=(int)($data['started']??0);
 $fails=(int)($data['count']??0);
 if(time()-$period>900){$period=time();$fails=0;}
 if($fails>=5){flock($rate,LOCK_UN);fclose($rate);http_response_code(429);exit('Too many attempts');}
 $hashFile=AUTH_DIR.'/password.hash';
 $hash=is_file($hashFile)?trim((string)file_get_contents($hashFile)):'';
 $valid=$hash!==''&&password_verify((string)($_POST['password']??''),$hash);
 $updated=json_encode(['started'=>$period,'count'=>$valid?0:$fails+1]);
 rewind($rate);ftruncate($rate,0);fwrite($rate,$updated);fflush($rate);flock($rate,LOCK_UN);fclose($rate);
 if($valid){
 session_regenerate_id(true);$_SESSION['authenticated']=true;$_SESSION['last']=time();
 header('Location: index.php',true,303);exit;
 }
 $wrong=true;
}
?><!doctype html><html lang="he" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Panda BC — כניסה</title>
<style>body{background:#0f172a;color:#f8fafc;min-height:100vh;display:grid;place-items:center;font:16px system-ui;margin:0}main{padding:32px;border:1px solid #475569;border-radius:16px;background:#1e293b;width:min(85vw,360px)}input,button{display:block;width:100%;box-sizing:border-box;padding:12px;margin:12px 0;border-radius:8px;font:inherit}input{background:#0f172a;color:white;border:1px solid #94a3b8}button{background:#2563eb;color:white;border:0;cursor:pointer}</style>
<main><h1>🐼 Panda BC</h1><h2>כניסה למנהל האתר</h2><?php if($wrong):?><p role="alert">סיסמה שגויה</p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars(token(),ENT_QUOTES)?>"><label for="password">סיסמה</label><input type="password" name="password" id="password" required autocomplete="current-password"><button>כניסה</button></form></main></html>
