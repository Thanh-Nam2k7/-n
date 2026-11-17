<?php
require __DIR__.'/app/bootstrap.php';$order=null;$orderItems=[];$errors=[];
if(post()){check_csrf();$code=strtoupper(trim((string)($_POST['code']??'')));$phone=trim((string)($_POST['phone']??''));
 if($code===''||$phone==='')$errors[]='Vui lòng nhập mã đơn và số điện thoại.';
 else{$order=one('SELECT * FROM orders WHERE order_code=? AND customer_phone=?',[$code,$phone]);if(!$order)$errors[]='Không tìm thấy đơn hàng tương ứng với mã và số điện thoại.';else $orderItems=all('SELECT * FROM order_items WHERE order_id=?',[$order['id']]);}
}
$page_title='Tra cứu đơn hàng | H2SO4 PHONES';require __DIR__.'/app/partials/header.php';
?>
<main class="container"><div class="form-card"><span class="eyebrow"><i data-lucide="package-search"></i> TRACK YOUR ORDER</span><h1 style="margin-top:15px">Tra cứu đơn hàng</h1><p>Nhập mã đơn hàng và số điện thoại người nhận để xem trạng thái.</p><?php if($errors):?><div class="error-list"><?= e(implode(' ',$errors)) ?></div><?php endif;?><form method="post"><?= csrf_field() ?><div class="form-group"><label>Mã đơn hàng</label><input name="code" required placeholder="H2S..."></div><div class="form-group"><label>Số điện thoại đặt hàng</label><input name="phone" required placeholder="09xxxxxxxx"></div><button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Tra cứu</button></form></div>
<?php if($order):?><div class="lookup-result"><?php require __DIR__.'/app/partials/order_details.php';?></div><?php endif;?></main>
<?php require __DIR__.'/app/partials/footer.php';?>
