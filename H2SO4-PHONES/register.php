<?php
require __DIR__.'/app/bootstrap.php';
if(user())redirect('account.php');$errors=[];$name='';$email='';$phone='';
if(post()){
 check_csrf();$name=trim((string)($_POST['name']??''));$email=strtolower(trim((string)($_POST['email']??'')));$phone=trim((string)($_POST['phone']??''));$password=(string)($_POST['password']??'');$repeat=(string)($_POST['repeat_password']??'');
 if(mb_strlen($name)<2||mb_strlen($name)>120)$errors[]='Tên phải có 2–120 ký tự.';
 if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>180)$errors[]='Email không hợp lệ.';
 if($phone!==''&&!preg_match('/^\+?[0-9\s.\-]{9,17}$/',$phone))$errors[]='Số điện thoại không hợp lệ.';
 if(strlen($password)<8)$errors[]='Mật khẩu phải có ít nhất 8 ký tự.';
 if($password!==$repeat)$errors[]='Hai mật khẩu không trùng nhau.';
 if(!$errors && one('SELECT id FROM users WHERE email=?',[$email]))$errors[]='Email này đã được sử dụng.';
 if(!$errors){try{query('INSERT INTO users(name,email,phone,password_hash) VALUES(?,?,?,?)',[$name,$email,$phone?:null,password_hash($password,PASSWORD_DEFAULT)]);session_regenerate_id(true);$_SESSION['user_id']=(int)db()->lastInsertId();flash('success','Đăng ký thành công!');redirect('account.php');}catch(PDOException $ex){$errors[]='Không thể đăng ký, có thể email đã tồn tại.';}}
}
$page_title='Đăng ký | H2SO4 PHONES';require __DIR__.'/app/partials/header.php';
?>
<main class="container"><div class="form-card"><div class="eyebrow"><i data-lucide="sparkles"></i> JOIN THE CLUB</div><h1 style="margin-top:16px">Tạo tài khoản mới</h1><p>Gia nhập H2SO4 PHONES và theo dõi đơn hàng dễ dàng.</p><?php if($errors):?><div class="error-list"><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul></div><?php endif;?><form method="post"><?= csrf_field() ?><div class="form-group"><label>Họ và tên *</label><input name="name" required maxlength="120" value="<?= e($name) ?>"></div><div class="form-group"><label>Email *</label><input name="email" type="email" required value="<?= e($email) ?>"></div><div class="form-group"><label>Số điện thoại</label><input name="phone" value="<?= e($phone) ?>"></div><div class="form-row"><div class="form-group"><label>Mật khẩu (8+ ký tự) *</label><input name="password" type="password" autocomplete="new-password" required minlength="8"></div><div class="form-group"><label>Nhập lại mật khẩu *</label><input name="repeat_password" type="password" autocomplete="new-password" required></div></div><button class="btn btn-primary" type="submit">Tạo tài khoản <i data-lucide="arrow-right"></i></button></form><div class="form-switch">Đã có tài khoản? <a href="<?= url('login.php') ?>">Đăng nhập</a></div></div></main>
<?php require __DIR__.'/app/partials/footer.php';?>
