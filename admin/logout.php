<?php
declare(strict_types=1);
require __DIR__.'/common.php';start_admin();authenticated();
if($_SERVER['REQUEST_METHOD']!=='POST'||!hash_equals(token(),(string)($_POST['csrf']??''))){http_response_code(403);exit('Forbidden');}
$_SESSION=[];session_destroy();header('Location: login.php',true,303);
