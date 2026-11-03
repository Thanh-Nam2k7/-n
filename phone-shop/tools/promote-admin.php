<?php
// Chỉ chạy trong terminal, không mở bằng trình duyệt.
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$c=require __DIR__.'/../app/config.php';
$db=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$email=$argv[1]??'';
if(!filter_var($email,FILTER_VALIDATE_EMAIL))exit("Cách dùng: php tools/promote-admin.php email-da-dang-ky\n");
$s=$db->prepare("UPDATE users SET role='admin' WHERE email=?");$s->execute([$email]);
echo $s->rowCount()?"Đã cấp quyền admin.\n":"Không đổi: email chưa đăng ký hoặc đã là admin.\n";
