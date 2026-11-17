<aside class="panel account-nav"><div style="padding:5px 12px 16px"><strong><?= e($me['name']) ?></strong><p class="hint" style="margin:4px 0"><?= e($me['email']) ?></p></div>
<a class="<?= ($section??'')==='profile'?'active':'' ?>" href="<?= url('account.php') ?>"><i data-lucide="user-round"></i> Hồ sơ của tôi</a>
<a class="<?= ($section??'')==='orders'?'active':'' ?>" href="<?= url('orders.php') ?>"><i data-lucide="package"></i> Đơn hàng của tôi</a>
<a class="<?= ($section??'')==='wishlist'?'active':'' ?>" href="<?= url('wishlist.php') ?>"><i data-lucide="heart"></i> Yêu thích</a>
<?php if($me['role']==='admin'):?><a href="<?= url('admin/index.php') ?>"><i data-lucide="layout-dashboard"></i> Trang quản trị</a><?php endif;?>
<form action="<?= url('logout.php') ?>" method="post"><?= csrf_field() ?><button type="submit" class="btn btn-outline" style="width:100%;margin-top:16px"><i data-lucide="log-out"></i> Đăng xuất</button></form></aside>
