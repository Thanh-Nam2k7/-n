<?php
require __DIR__.'/app/bootstrap.php';$code=(string)($_GET['code']??'');
$order=one('SELECT * FROM orders WHERE order_code=? AND id=?',[$code,(int)($_SESSION['last_order_id']??0)]);
if(!$order){flash('error','Không thể hiển thị đơn hàng này. Hãy dùng chức năng tra cứu đơn hàng.');redirect('lookup.php');}
$orderItems=all('SELECT * FROM order_items WHERE order_id=?',[$order['id']]);$page_title='Đặt hàng thành công | H2SO4 PHONES';require __DIR__.'/app/partials/header.php';
?>
<main class="container"><div style="text-align:center;padding:48px 0 18px"><span style="display:inline-grid;place-items:center;border-radius:50%;background:#1c4a40;width:84px;height:84px;color:var(--mint)"><i data-lucide="check-circle-2" style="width:46px;height:46px"></i></span><h1 class="page-title" style="margin-top:18px">Đặt hàng <span style="color:var(--mint)">thành công!</span></h1><p class="page-subtitle">Cảm ơn bạn! Đơn hàng đã được ghi nhận trong hệ thống demo.</p><div style="margin-top:18px"><a class="btn btn-primary" href="<?= url('catalog.php') ?>">Tiếp tục mua sắm</a> <a class="btn btn-outline" href="<?= url('lookup.php') ?>">Tra cứu đơn hàng</a></div></div><?php require __DIR__.'/app/partials/order_details.php'; ?></main>
<?php require __DIR__.'/app/partials/footer.php';?>
