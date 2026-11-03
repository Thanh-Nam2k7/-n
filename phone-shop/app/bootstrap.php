<?php
declare(strict_types=1);
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','samesite'=>'Lax']);
session_start();
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');
set_exception_handler(function(Throwable $e) { error_log((string)$e); http_response_code(500); exit('Có lỗi hệ thống. Kiểm tra cấu hình database và nhật ký PHP.'); });
$c=require __DIR__.'/config.php';
$db=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
function query(string $sql,array $args=[]): PDOStatement { global $db; $s=$db->prepare($sql);$s->execute($args);return $s; }
function e($v): string {return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function money($v): string {return number_format((float)$v,0,',','.').' đ';}
function go(string $url): void {header('Location: '.$url);exit;}
function input(string $k): string {return is_string($_POST[$k]??null)?trim($_POST[$k]):'';}
function integer($v,int $min=0,int $max=1000000000): int { $n=filter_var($v,FILTER_VALIDATE_INT); if($n===false||$n<$min||$n>$max)throw new DomainException('Giá trị số không hợp lệ.');return $n; }
function csrf(): string {$_SESSION['csrf']??=bin2hex(random_bytes(32));return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';}
function check_csrf(): void {if(empty($_SESSION['csrf'])||!is_string($_POST['csrf']??null)||!hash_equals($_SESSION['csrf'],$_POST['csrf'])){http_response_code(403);exit('Phiên biểu mẫu hết hạn. Hãy tải lại trang.');}}
function user(): ?array {static $u=false;if($u===false)$u=isset($_SESSION['uid'])?(query('SELECT id,name,email,role FROM users WHERE id=?',[$_SESSION['uid']])->fetch()?:null):null;return $u;}
function auth(bool $admin=false): void {if(!user())go('index.php?page=login');if($admin&&user()['role']!=='admin'){http_response_code(403);exit('Bạn không có quyền truy cập.');}}
function flash(string $s): void {$_SESSION['flash']=$s;}
function field(string $name,string $label,$value='',string $type='text',bool $required=true): void {echo '<label class="form-label d-block">'.e($label).'<input class="form-control" type="'.e($type).'" name="'.e($name).'" value="'.e($value).'" '.($required?'required':'').'></label>';}
const STATUSES=['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','shipping'=>'Đang giao','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
function cart_rows(): array {$rows=[];foreach($_SESSION['cart']??[] as $id=>$qty){$p=query('SELECT * FROM products WHERE id=?',[$id])->fetch();if($p){$p['qty']=$qty;$rows[]=$p;}}return $rows;}
