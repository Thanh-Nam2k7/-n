<?php
require __DIR__.'/../app/bootstrap.php';$admin=require_admin();$id=(int)($_GET['id']??0);$errors=[];
$flow=['pending'=>['confirmed','cancelled'],'confirmed'=>['shipping','cancelled'],'shipping'=>['completed','cancelled'],'completed'=>[],'cancelled'=>[]];
if(post()){
 check_csrf();$newStatus=(string)($_POST['status']??'');$payment=(string)($_POST['payment_status']??'unpaid');
 if(!in_array($payment,['paid','unpaid'],true))$errors[]='Trạng thái thanh toán không hợp lệ.';
 if(!$errors){try{
    db()->beginTransaction();
    $locked=one('SELECT * FROM orders WHERE id=? FOR UPDATE',[$id]);
    if(!$locked)throw new RuntimeException('Đơn hàng không tồn tại.');
    $oldStatus=$locked['status'];
    if($newStatus!==$oldStatus && !in_array($newStatus,$flow[$oldStatus]??[],true))throw new RuntimeException('Không thể chuyển trạng thái từ '.format_order_status($oldStatus).' sang '.format_order_status($newStatus).'.');
    if($newStatus==='cancelled' && $oldStatus!=='cancelled'){
      // Hoàn kho đúng 1 lần khi hủy đơn; product_id NULL nếu sản phẩm đã bị xóa.
      $lines=all('SELECT product_id,quantity FROM order_items WHERE order_id=?',[$id]);
      foreach($lines as $line){if($line['product_id'])query('UPDATE products SET stock=stock+? WHERE id=?',[$line['quantity'],$line['product_id']]);}
    }
    if($locked['payment_status']==='paid'&&$payment==='unpaid')throw new RuntimeException('Đơn đã trả tiền không thể đổi về chưa trả trong giao diện demo.');
    if($newStatus==='cancelled'&&$payment==='paid')throw new RuntimeException('Không thể đánh dấu đã thanh toán cho đơn đã hủy.');
    query('UPDATE orders SET status=?,payment_status=? WHERE id=?',[$newStatus,$payment,$id]);
    db()->commit();flash('success','Đã cập nhật đơn hàng.');redirect('admin/order_detail.php?id='.$id);
 }catch(Throwable $ex){if(db()->inTransaction())db()->rollBack();$errors[]=$ex instanceof RuntimeException?$ex->getMessage():'Không thể cập nhật đơn hàng.';}}
}
$order=one('SELECT * FROM orders WHERE id=?',[$id]);if(!$order){http_response_code(404);exit('Không tìm thấy đơn hàng.');}
$items=all('SELECT * FROM order_items WHERE order_id=?',[$id]);$admin_title='Chi tiết đơn '.$order['order_code'];$admin_menu='orders';require __DIR__.'/partials/header.php';
?>
<a class="section-more" href="<?= url('admin/orders.php') ?>" style="margin-bottom:18px"><i data-lucide="arrow-left"></i> Trở lại đơn hàng</a><?php if($errors):?><div class="error-list"><?= e(implode(' ',$errors)) ?></div><?php endif;?>
<div class="admin-grid-2"><section class="admin-panel"><h2>Chi tiết sản phẩm</h2><table class="admin-table"><thead><tr><th>Tên sản phẩm</th><th>SKU</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th></tr></thead><tbody><?php foreach($items as $item):?><tr><td><strong><?= e($item['product_name']) ?></strong></td><td><?= e($item['product_sku']) ?></td><td><?= money($item['price']) ?></td><td><?= (int)$item['quantity'] ?></td><td><?= money($item['line_total']) ?></td></tr><?php endforeach;?></tbody></table><div style="max-width:300px;margin:20px 0 0 auto"><div class="summary-row"><span>Tạm tính</span><strong><?= money($order['subtotal']) ?></strong></div><div class="summary-row"><span>Giao hàng</span><strong><?= money($order['shipping_fee']) ?></strong></div><div class="summary-row total"><span>Tổng cộng</span><strong><?= money($order['total']) ?></strong></div></div></section>
<div><section class="admin-panel"><h2>Cập nhật trạng thái</h2><form method="post" class="admin-form"><?= csrf_field() ?><div class="form-group"><label>Trạng thái đơn</label><select name="status"><option value="<?= e($order['status']) ?>"><?= e(format_order_status($order['status'])) ?> (hiện tại)</option><?php foreach($flow[$order['status']] as $s):?><option value="<?= e($s) ?>"><?= e(format_order_status($s)) ?></option><?php endforeach;?></select></div><div class="form-group"><label>Thanh toán</label><select name="payment_status"><option value="unpaid" <?= $order['payment_status']==='unpaid'?'selected':'' ?>>Chưa thanh toán</option><option value="paid" <?= $order['payment_status']==='paid'?'selected':'' ?>>Đã thanh toán</option></select></div><button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Lưu trạng thái</button></form><p class="admin-order-note" style="margin-top:16px">Luồng đơn: Chờ xác nhận → Đã xác nhận → Đang giao → Hoàn tất. Hủy đơn sẽ tự hoàn tồn kho đúng một lần.</p></section>
<section class="admin-panel"><h2>Người nhận</h2><p class="hint"><strong><?= e($order['customer_name']) ?></strong></p><p class="hint">SĐT: <?= e($order['customer_phone']) ?></p><p class="hint">Email: <?= e($order['customer_email']?:'Không có') ?></p><p class="hint">Địa chỉ: <?= e($order['address']) ?></p><p class="hint">Ghi chú: <?= e($order['note']?:'Không có') ?></p><p class="hint">Ngày đặt: <?= date('d/m/Y H:i',strtotime($order['created_at'])) ?></p></section></div></div>
<?php require __DIR__.'/partials/footer.php';?>
