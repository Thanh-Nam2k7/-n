<?php
require __DIR__.'/app/bootstrap.php';
if(user())redirect('account.php');$errors=[];$email='';
if(post()){check_csrf();$email=trim((string)($_POST['email']??''));$password=(string)($_POST['password']??'');
    $u=one('SELECT id,password_hash,is_active FROM users WHERE email=?',[$email]);
    if(!$u||!$u['is_active']||!password_verify($password,$u['password_hash']))$errors[]='Email hoặc mật khẩu không chính xác.';
    else{session_regenerate_id(true);$_SESSION['user_id']=(int)$u['id'];flash('success','Đăng nhập thành công!');redirect('account.php');}
}
$page_title='Đăng nhập | H2SO4 PHONES';require __DIR__.'/app/partials/header.php';
?>
<main class="container"><div class="form-card"><div class="eyebrow"><i data-lucide="lock-keyhole"></i> MEMBER ZONE</div><h1 style="margin-top:16px">Chào mừng trở lại!</h1><p>Đăng nhập để theo dõi đơn hàng và lưu sản phẩm yêu thích.</p><?php if($errors):?><div class="error-list"><?= e(implode(' ', $errors)) ?></div><?php endif;?><form method="post"><?= csrf_field() ?><div class="form-group"><label>Email</label><input name="email" type="email" required autocomplete="email" value="<?= e($email) ?>" placeholder="you@example.com"></div><div class="form-group"><label>Mật khẩu</label><input type="password" name="password" required autocomplete="current-password" placeholder="Nhập mật khẩu của bạn"></div><button class="btn btn-primary" type="submit"><i data-lucide="log-in"></i> Đăng nhập</button></form><div class="form-switch">Chưa có tài khoản? <a href="<?= url('register.php') ?>">Đăng ký miễn phí</a></div></div></main>
<?php require __DIR__.'/app/partials/footer.php';?>
