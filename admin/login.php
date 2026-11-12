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
?><!doctype html>
<html lang="he" dir="rtl"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="dark">
<title>BC Control Center — כניסה</title>
<link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{color-scheme:dark}*{box-sizing:border-box}body{min-height:100vh;margin:0;background:#0c1017;color:#f0f3f7;font:14px/1.5 Heebo,Arial,sans-serif;display:grid;place-items:center;padding:28px}body:before{content:"";position:fixed;inset:0;background:radial-gradient(ellipse at 50% -20%,#34435555,transparent 58%);pointer-events:none}.layout{position:relative;width:min(100%,420px)}.brand{display:flex;align-items:center;gap:15px;justify-content:center;margin-bottom:35px}.brand-mark{font:800 43px Arial;letter-spacing:-3px}.brand-mark span{color:#cba875}.brand strong{display:block;letter-spacing:2px;font-size:12px}.brand small{font:10px Arial;letter-spacing:2px;color:#94a4b7}.panel{border:1px solid #303b49;background:#171e29;padding:38px;border-radius:13px;box-shadow:0 35px 90px #0005}.eyebrow{color:#cba875;font-size:10px;letter-spacing:2px;font-weight:700}h1{font-size:27px;margin:12px 0 8px;line-height:1.3}p{color:#98a5b6;margin:0 0 27px}label{display:block;font-weight:600;margin-bottom:9px}input{width:100%;background:#101722;color:white;border:1px solid #3d4a59;border-radius:8px;padding:14px;font:inherit;outline:0}input:focus{border-color:#cba875;box-shadow:0 0 0 3px #cba87522}button{margin-top:22px;width:100%;background:#cba875;border:0;color:#151a20;font:700 15px Heebo,Arial;border-radius:8px;padding:14px;cursor:pointer}button:hover{background:#e2bf8a}.error{background:#37252a;border:1px solid #75474c;padding:13px;border-radius:7px;color:#ffc3c1;margin-bottom:17px}.foot{margin:22px 0;text-align:center;color:#788798;font:10px Arial;letter-spacing:1px}@media(max-width:480px){.panel{padding:26px}.brand{margin-bottom:26px}}
</style></head><body><div class="layout"><div class="brand"><span class="brand-mark">BC<span>.</span></span><span><strong>CONTROL CENTER</strong><small>ADMINISTRATION / V2</small></span></div><main class="panel"><div class="eyebrow">SECURE ACCESS</div><h1>כניסה למערכת הניהול</h1><p>גישה פרטית לעריכת הנוכחות הדיגיטלית.</p><?php if($wrong):?><div class="error" role="alert">פרטי הכניסה שגויים. נסה שוב.</div><?php endif;?><form method="post" autocomplete="on"><input type="hidden" name="csrf" value="<?=htmlspecialchars(token(),ENT_QUOTES)?>"><label for="password">סיסמה</label><input type="password" name="password" id="password" autocomplete="current-password" required autofocus><button type="submit">כניסה מאובטחת ←</button></form></main><div class="foot">BC / PRIVATE ADMINISTRATION</div></div></body></html>