<?php
require __DIR__.'/app/bootstrap.php';$me=require_login();$errors=[];
if(post()){
 check_csrf();$action=(string)($_POST['action']??'profile');
 if($action==='profile'){
   $name=trim((string)($_POST['name']??''));$phone=trim((string)($_POST['phone']??''));
   if(mb_strlen($name)<2||mb_strlen($name)>120)$errors[]='Họ tên phải từ 2–120 ký tự.';
   if($phone!==''&&!preg_match('/^\+?[0-9\s.\-]{9,17}$/',$phone))$errors[]='Số điện thoại không hợp lệ.';
   if(!$errors){query('UPDATE users SET name=?,phone=? WHERE id=?',[$name,$phone?:null,$me['id']]);flash('success','Cập nhật thông tin thành công.');redirect('account.php');}
 }elseif($action==='password'){
   $old=(string)($_POST['old_password']??'');$new=(string)($_POST['new_password']??'');
   $hash=one('SELECT password_hash FROM users WHERE id=?',[$me['id']]);
   if(!$hash||!password_verify($old,$hash['password_hash']))$errors[]='Mật khẩu hiện tại không chính xác.';
   if(strlen($new)<8)$errors[]='Mật khẩu mới phải có ít nhất 8 ký tự.';
   if(!$errors){query('UPDATE users SET password_hash=? WHERE id=?',[password_hash($new,PASSWORD_DEFAULT),$me['id']]);flash('success','Đã đổi mật khẩu.');redirect('account.php');}
 }
}
$section='profile';$page_title='Tài khoản của tôi | H2SO4 PHONES';require __DIR__.'/app/partials/header.php';
?>
<main class="container"><div class="breadcrumb"><a href="<?= url('index.php') ?>">Trang chủ</a><i data-lucide="chevron-right"></i>Tài khoản</div><h1 class="page-title">Xin chào, <span style="color:var(--mint)"><?= e($me['name']) ?></span> 👋</h1><div class="account-layout"><?php require __DIR__.'/app/partials/account_nav.php';?><div><div class="panel"><h3 style="margin-top:0">Thông tin cá nhân</h3><?php if($errors):?><div class="error-list"><?= e(implode(' ',$errors)) ?></div><?php endif;?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="profile"><div class="form-group"><label>Họ tên</label><input name="name" required value="<?= e($me['name']) ?>"></div><div class="form-group"><label>Email (không thể chỉnh sửa)</label><input value="<?= e($me['email']) ?>" disabled></div><div class="form-group"><label>Số điện thoại</label><input name="phone" value="<?= e($me['phone']) ?>"></div><button type="submit" class="btn btn-primary">Lưu thay đổi</button></form></div><div class="panel" style="margin-top:20px"><h3 style="margin-top:0">Đổi mật khẩu</h3><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="password"><div class="form-group"><label>Mật khẩu hiện tại</label><input type="password" name="old_password" required autocomplete="current-password"></div><div class="form-group"><label>Mật khẩu mới (ít nhất 8 ký tự)</label><input type="password" name="new_password" required minlength="8" autocomplete="new-password"></div><button type="submit" class="btn btn-outline">Đổi mật khẩu</button></form></div></div></div></main>
<?php require __DIR__.'/app/partials/footer.php';?>
