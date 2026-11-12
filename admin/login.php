<?php
declare(strict_types=1);
require __DIR__.'/common.php';start_admin();
if(!empty($_SESSION['authenticated'])){header('Location: index.php',true,303);exit;}
$wrong=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!hash_equals(token(),(string)($_POST['csrf']??''))){http_response_code(403);exit('Invalid request');}
 if(time()-(int)($_SESSION['fail_start']??0)>900){$_SESSION['attempts']=0;$_SESSION['fail_start']=time();}
 if((int)($_SESSION['attempts']??0)>=5){http_response_code(429);exit('Too many attempts');}
 $hashFile=AUTH_DIR.'/password.hash';$hash=is_file($hashFile)?trim((string)file_get_contents($hashFile)):'';
 if($hash!==''&&password_verify((string)($_POST['password']??''),$hash)){
 session_regenerate_id(true);$_SESSION['authenticated']=true;$_SESSION['last']=time();$_SESSION['attempts']=0;
 header('Location: index.php',true,303);exit;
 }
 $_SESSION['attempts']=1+(int)($_SESSION['attempts']??0);$wrong=true;
}
?><!doctype html><html lang="he" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Panda BC — כניסה</title>
<style>body{background:#0f172a;color:#f8fafc;min-height:100vh;display:grid;place-items:center;font:16px system-ui;margin:0}main{padding:32px;border:1px solid #475569;border-radius:16px;background:#1e293b;width:min(85vw,360px)}input,button{display:block;width:100%;box-sizing:border-box;padding:12px;margin:12px 0;border-radius:8px;font:inherit}input{background:#0f172a;color:white;border:1px solid #94a3b8}button{background:#2563eb;color:white;border:0;cursor:pointer}</style>
<main><h1>🐼 Panda BC</h1><h2>כניסה למנהל האתר</h2><?php if($wrong):?><p role="alert">סיסמה שגויה</p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars(token(),ENT_QUOTES)?>"><label for="password">סיסמה</label><input type="password" name="password" id="password" required autocomplete="current-password"><button>כניסה</button></form></main></html>
