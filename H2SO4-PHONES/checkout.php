<?php
require __DIR__.'/app/bootstrap.php';
$items=cart_items();if(!$items){flash('error','Giỏ hàng đang trống.');redirect('cart.php');}
$me=user();$errors=[];
$data=['customer_name'=>$_POST['customer_name']??($me['name']??''),'customer_email'=>$_POST['customer_email']??($me['email']??''),'customer_phone'=>$_POST['customer_phone']??($me['phone']??''),'address'=>$_POST['address']??'','note'=>$_POST['note']??'','payment_method'=>$_POST['payment_method']??'cod'];
if(post()){
    check_csrf(); foreach($data as $k=>$v)$data[$k]=trim((string)$v);
    if(mb_strlen($data['customer_name'])<2 || mb_strlen($data['customer_name'])>120)$errors[]='Họ tên phải có từ 2 đến 120 ký tự.';
    if($data['customer_email']!==''&&!filter_var($data['customer_email'],FILTER_VALIDATE_EMAIL))$errors[]='Email không hợp lệ.';
    if(!preg_match('/^\+?[0-9\s.\-]{9,17}$/',$data['customer_phone']))$errors[]='Số điện thoại không hợp lệ.';
    if(mb_strlen($data['address'])<10||mb_strlen($data['address'])>400)$errors[]='Địa chỉ giao hàng cần ít nhất 10 ký tự (tối đa 400).';
    if(mb_strlen($data['note'])>600)$errors[]='Ghi chú quá dài.';
    if(!in_array($data['payment_method'],['cod','bank_transfer'],true))$errors[]='Hình thức thanh toán không hợp lệ.';
    if(!$errors){
      try {
        db()->beginTransaction();
        $locked=[];$subtotal=0;
        // Khóa hàng theo ID tăng dần để hạn chế deadlock khi nhiều người mua cùng lúc.
        $basket=$_SESSION['cart']??[]; ksort($basket,SORT_NUMERIC);
        foreach($basket as $id=>$qty){
            $qty=max(1,min(99,(int)$qty));
            $row=one('SELECT id,name,sku,price,stock,is_active FROM products WHERE id=? FOR UPDATE',[(int)$id]);
            if(!$row||!$row['is_active']||$row['stock']<$qty)throw new RuntimeException('Một sản phẩm không còn đủ số lượng. Vui lòng cập nhật giỏ hàng.');
            $row['qty']=$qty;$row['line_total']=$qty*(int)$row['price'];$locked[]=$row;$subtotal+=$row['line_total'];
        }
        if(!$locked)throw new RuntimeException('Giỏ hàng trống.');
        $shipping=shipping_cost($subtotal);$code='H2S'.date('ymd').strtoupper(bin2hex(random_bytes(5)));
        query('INSERT INTO orders (order_code,user_id,customer_name,customer_email,customer_phone,address,note,subtotal,shipping_fee,total,payment_method) VALUES (?,?,?,?,?,?,?,?,?,?,?)',[
          $code,$me['id']??null,$data['customer_name'],$data['customer_email']?:null,$data['customer_phone'],$data['address'],$data['note']?:null,
          $subtotal,$shipping,$subtotal+$shipping,$data['payment_method']]);
        $orderId=(int)db()->lastInsertId();
        foreach($locked as $item){
            query('INSERT INTO order_items(order_id,product_id,product_name,product_sku,price,quantity,line_total) VALUES(?,?,?,?,?,?,?)',[
               $orderId,$item['id'],$item['name'],$item['sku'],$item['price'],$item['qty'],$item['line_total']]);
            query('UPDATE products SET stock=stock-? WHERE id=?',[$item['qty'],$item['id']]);
        }
        db()->commit();unset($_SESSION['cart']);$_SESSION['last_order_id']=$orderId;
        redirect('success.php?code='.urlencode($code));
      }catch(Throwable $ex){if(db()->inTransaction())db()->rollBack();error_log('Checkout: '.$ex->getMessage());$errors[]=$ex instanceof RuntimeException ? $ex->getMessage() : 'Không thể tạo đơn hàng. Vui lòng thử lại.';}
    }
}
$subtotal=cart_subtotal($items);$shipping=shipping_cost($subtotal);$page_title='Thanh toán | H2SO4 PHONES';
require __DIR__.'/app/partials/header.php';
?>
<main class="container"><div class="breadcrumb"><a href="<?= url('cart.php') ?>">Giỏ hàng</a><i data-lucide="chevron-right"></i>Thanh toán</div><h1 class="page-title">Hoàn tất <span style="color:var(--mint)">đơn hàng</span></h1><p class="page-subtitle">Chỉ còn một bước nữa là xong!</p>
<?php if($errors):?><div class="error-list" style="margin-top:18px"><ul><?php foreach($errors as $error):?><li><?= e($error) ?></li><?php endforeach;?></ul></div><?php endif;?>
<div class="checkout-layout"><div class="panel"><form id="checkout-form" action="<?= url('checkout.php') ?>" method="post"><?= csrf_field() ?><h2><i data-lucide="map-pin"></i> Thông tin nhận hàng</h2><div class="form-row"><div class="form-group"><label>Họ và tên *</label><input required maxlength="120" name="customer_name" value="<?= e($data['customer_name']) ?>" placeholder="Nguyễn Văn A"></div><div class="form-group"><label>Số điện thoại *</label><input required name="customer_phone" maxlength="17" value="<?= e($data['customer_phone']) ?>" placeholder="09xxxxxxxx"></div></div><div class="form-group"><label>Email (nhận thông tin liên hệ, không bắt buộc)</label><input name="customer_email" type="email" value="<?= e($data['customer_email']) ?>" placeholder="email@example.com"></div><div class="form-group"><label>Địa chỉ giao hàng *</label><textarea required name="address" maxlength="400" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố"><?= e($data['address']) ?></textarea></div><div class="form-group"><label>Ghi chú cho đơn hàng</label><textarea name="note" maxlength="600" placeholder="Yêu cầu thêm (nếu có)"><?= e($data['note']) ?></textarea></div><h2><i data-lucide="wallet"></i> Phương thức thanh toán</h2><label class="payment-option"><input type="radio" name="payment_method" value="cod" <?= $data['payment_method']==='cod'?'checked':'' ?>> <span><strong>Thanh toán khi nhận hàng (COD)</strong><br><small class="hint">Chỉ là mô phỏng đơn hàng cho đồ án</small></span></label><label class="payment-option"><input type="radio" name="payment_method" value="bank_transfer" <?= $data['payment_method']==='bank_transfer'?'checked':'' ?>><span><strong>Chuyển khoản ngân hàng (demo)</strong><br><small class="hint">Chưa tích hợp ngân hàng / cổng thanh toán thật</small></span></label></form></div>
<aside><div class="panel summary-box"><h3>Đơn hàng của bạn</h3><?php foreach($items as $p):?><div class="summary-row"><span><?= e(cut($p['name'],27)) ?> × <?= $p['qty'] ?></span><strong><?= money($p['total']) ?></strong></div><?php endforeach;?><div class="summary-row" style="border-top:1px solid var(--border)"><span>Tạm tính</span><strong><?= money($subtotal) ?></strong></div><div class="summary-row"><span>Phí vận chuyển</span><strong><?= $shipping===0?'Miễn phí':money($shipping) ?></strong></div><div class="summary-row total"><span>Cần thanh toán</span><strong><?= money($subtotal+$shipping) ?></strong></div><button class="btn btn-primary" type="submit" form="checkout-form"><i data-lucide="check-circle"></i> Xác nhận đặt hàng</button><p class="hint">Thông tin này chỉ được lưu trong cơ sở dữ liệu demo tại máy của bạn.</p></div></aside></div></main>
<?php require __DIR__.'/app/partials/footer.php';?>
