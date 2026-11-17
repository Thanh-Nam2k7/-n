<?php
require __DIR__.'/../app/bootstrap.php';$admin=require_admin();$q=trim((string)($_GET['q']??''));
if(post()){
 check_csrf();$id=(int)($_POST['id']??0);$target=one('SELECT id,role FROM users WHERE id=?',[$id]);
 if(!$target||$target['role']==='admin'||$id===$admin['id'])flash('error','Không thể vô hiệu hóa tài khoản Admin.');
 else{query('UPDATE users SET is_active=1-is_active WHERE id=?',[$id]);flash('success','Đã đổi trạng thái tài khoản.');}redirect('admin/users.php');
}
$users=all('SELECT id,name,email,phone,role,is_active,created_at FROM users'.($q?' WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?':'').' ORDER BY id DESC LIMIT 300',$q?['%'.$q.'%','%'.$q.'%','%'.$q.'%']:[]);
$admin_title='Quản lý tài khoản';$admin_menu='users';require __DIR__.'/partials/header.php';
?>
<div class="admin-panel"><div class="admin-toolbar"><form method="get"><input name="q" type="search" placeholder="Tìm khách hàng..." value="<?= e($q) ?>"><button class="btn btn-outline" type="submit">Tìm</button></form></div><table class="admin-table"><thead><tr><th>ID</th><th>Họ tên</th><th>Email</th><th>SĐT</th><th>Vai trò</th><th>Ngày đăng ký</th><th>Trạng thái</th><th></th></tr></thead><tbody><?php foreach($users as $u):?><tr><td><?= $u['id'] ?></td><td><strong><?= e($u['name']) ?></strong></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']?:'—') ?></td><td><?= $u['role']==='admin'?'Admin':'Khách hàng' ?></td><td><?= date('d/m/Y',strtotime($u['created_at'])) ?></td><td><span class="status <?= $u['is_active']?'completed':'cancelled' ?>"><?= $u['is_active']?'Hoạt động':'Đã khóa' ?></span></td><td><?php if($u['role']==='customer'):?><div class="admin-actions"><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>"><button type="submit" data-confirm="Bạn muốn đổi trạng thái tài khoản này?"><?= $u['is_active']?'Khóa':'Mở' ?></button></form></div><?php endif;?></td></tr><?php endforeach;?></tbody></table></div>
<?php require __DIR__.'/partials/footer.php';?>
