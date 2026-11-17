<?php
require __DIR__.'/app/bootstrap.php';$me=require_login();$id=(int)($_GET['id']??0);
$order=one('SELECT * FROM orders WHERE id=? AND user_id=?',[$id,$me['id']]);if(!$order){http_response_code(404);exit('Không tìm thấy đơn hàng này.');}
$orderItems=all('SELECT * FROM order_items WHERE order_id=?',[$order['id']]);$page_title='Đơn '.$order['order_code'].' | H2SO4 PHONES';require __DIR__.'/app/partials/header.php';
?>
<main class="container"><div class="breadcrumb"><a href="<?= url('orders.php') ?>">Đơn hàng của tôi</a><i data-lucide="chevron-right"></i><?= e($order['order_code']) ?></div><?php require __DIR__.'/app/partials/order_details.php'; ?></main>
<?php require __DIR__.'/app/partials/footer.php';?>
