<?php
require __DIR__.'/app/bootstrap.php';
// Installer chạy một lần: sau khi đã có quản trị viên, tính năng này vô hiệu.
$adminCount=(int)(one("SELECT COUNT(*) AS total FROM users WHERE role='admin'")['total']??0);
$errors=[];$done=$adminCount>0;
if(post() && !$done){
 check_csrf();$name=trim((string)($_POST['name']??''));$email=strtolower(trim((string)($_POST['email']??'')));$password=(string)($_POST['password']??'');$confirm=(string)($_POST['confirm']??'');
 if(mb_strlen($name)<2||mb_strlen($name)>120)$errors[]='Tên quản trị viên cần 2–120 ký tự.';
 if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Email không hợp lệ.';
 if(strlen($password)<10)$errors[]='Mật khẩu quản trị viên cần ít nhất 10 ký tự.';
 if($password!==$confirm)$errors[]='Mật khẩu nhập lại không khớp.';
 if(one('SELECT id FROM users WHERE email=?',[$email]))$errors[]='Email đã được sử dụng.';
 if(!$errors){
    try{
      query("INSERT INTO users(name,email,phone,password_hash,role) VALUES(?,?,?,?, 'admin')",[$name,$email,null,password_hash($password,PASSWORD_DEFAULT)]);
      flash('success','Đã tạo tài khoản Admin. Hãy đăng nhập và xóa setup.php khi triển khai thật.');redirect('login.php');
    }catch(PDOException $ex){$errors[]='Không thể tạo tài khoản. Kiểm tra lại email và database.';}
 }
}
$page_title='Thiết lập quản trị viên | H2SO4 PHONES';require __DIR__.'/app/partials/header.php';
?>
<main class="container"><div class="form-card"><span class="eyebrow"><i data-lucide="settings"></i> FIRST TIME SETUP</span><h1 style="margin-top:18px">Khởi tạo tài khoản Admin</h1><?php if($done):?><div class="alert alert-success">Hệ thống đã có tài khoản quản trị viên. Trang tạo Admin đã bị khóa.</div><a class="btn btn-primary" href="<?= url('login.php') ?>">Đến trang đăng nhập</a><?php else:?><p>Dùng sau khi import SQL. Bạn tự đặt mật khẩu; hệ thống không dùng mật khẩu admin mặc định.</p><?php if($errors):?><div class="error-list"><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul></div><?php endif;?><form method="post"><?= csrf_field() ?><div class="form-group"><label>Họ và tên Admin</label><input name="name" required minlength="2" maxlength="120" value="<?= e((string)($_POST['name']??'')) ?>"></div><div class="form-group"><label>Email đăng nhập</label><input type="email" name="email" required value="<?= e((string)($_POST['email']??'')) ?>"></div><div class="form-group"><label>Mật khẩu (tối thiểu 10 ký tự)</label><input name="password" type="password" minlength="10" required autocomplete="new-password"></div><div class="form-group"><label>Nhập lại mật khẩu</label><input name="confirm" type="password" required autocomplete="new-password"></div><button class="btn btn-primary" type="submit">Tạo tài khoản quản trị <i data-lucide="arrow-right"></i></button></form><?php endif;?></div></main>
<?php require __DIR__.'/app/partials/footer.php';?>
