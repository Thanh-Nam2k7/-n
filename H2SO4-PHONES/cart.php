<?php
require __DIR__.'/app/bootstrap.php';
if(post()){
    check_csrf();$action=(string)($_POST['action']??'');
    if($action==='add'){
        $id=(int)($_POST['id']??0);$qty=max(1,min(99,(int)($_POST['qty']??1)));
        $p=one('SELECT id,name,stock FROM products WHERE id=? AND is_active=1',[$id]);
        if(!$p || (int)$p['stock']<1)flash('error','Sản phẩm đã hết hàng hoặc không tồn tại.');
        else {$old=(int)($_SESSION['cart'][$id]??0);$_SESSION['cart'][$id]=min((int)$p['stock'],99,$old+$qty);flash('success','Đã thêm '. $p['name'] .' vào giỏ hàng!');}
    }elseif($action==='update'){
        foreach(($_POST['qty']??[]) as $id=>$qty){$id=(int)$id;$q=(int)$qty;if($q<1){unset($_SESSION['cart'][$id]);continue;} $p=one('SELECT stock FROM products WHERE id=? AND is_active=1',[$id]);if($p && (int)$p['stock']>0){$_SESSION['cart'][$id]=min(99,(int)$p['stock'],$q);}else{unset($_SESSION['cart'][$id]);}}
        flash('success','Giỏ hàng đã được cập nhật.');
    }elseif($action==='remove'){unset($_SESSION['cart'][(int)($_POST['id']??0)]);flash('success','Đã xóa sản phẩm khỏi giỏ hàng.');}
    elseif($action==='clear'){unset($_SESSION['cart']);flash('success','Đã xóa toàn bộ giỏ hàng.');}
    redirect('cart.php');
}
$items=cart_items();$subtotal=cart_subtotal($items);$shipping=shipping_cost($subtotal);$page_title='Giỏ hàng | H2SO4 PHONES';
require __DIR__.'/app/partials/header.php';
?>
<main class="container"><div class="breadcrumb"><a href="<?= url('index.php') ?>">Trang chủ</a><i data-lucide="chevron-right"></i>Giỏ hàng</div><h1 class="page-title">Giỏ hàng <span style="color:var(--mint)">của bạn</span></h1><p class="page-subtitle">Có <?= cart_count() ?> sản phẩm đang chờ bạn.</p>
<?php if(!$items):?><div class="empty-state" style="margin-top:24px"><i data-lucide="shopping-bag"></i><h3>Giỏ hàng đang trống</h3><p>Đến lúc chọn một món đồ công nghệ bạn yêu thích rồi!</p><a class="btn btn-primary" href="<?= url('catalog.php') ?>">Khám phá sản phẩm</a></div><?php else:?>
<div class="cart-layout"><div class="panel"><form action="<?= url('cart.php') ?>" method="post" id="update-cart"><?= csrf_field() ?><input type="hidden" name="action" value="update">
<?php foreach($items as $p):?><div class="cart-row"><a href="<?= url('product.php?slug='.urlencode($p['slug'])) ?>"><img src="<?= e(product_image($p['image'])) ?>" alt="<?= e($p['name']) ?>"></a><div><h4><a href="<?= url('product.php?slug='.urlencode($p['slug'])) ?>"><?= e($p['name']) ?></a></h4><small><?= money($p['price']) ?> / sản phẩm</small><?php if(!$p['is_active'] || $p['stock']<$p['qty']):?><small style="color:var(--danger)">Chỉ còn <?= (int)$p['stock'] ?> sản phẩm</small><?php endif;?></div><div class="qty-form"><div class="quantity-control"><button type="button" data-qty-step="-1" data-qty-target="qty-<?= $p['id'] ?>">−</button><input id="qty-<?= $p['id'] ?>" type="number" min="0" max="<?= max(1,$p['stock']) ?>" name="qty[<?= $p['id'] ?>]" value="<?= (int)$p['qty'] ?>"><button type="button" data-qty-step="1" data-qty-target="qty-<?= $p['id'] ?>">+</button></div></div><strong class="line-total"><?= money($p['total']) ?></strong><span class="delete-form"><button class="delete-btn" type="submit" form="remove-<?= $p['id'] ?>" title="Xóa"><i data-lucide="trash-2"></i></button></span></div><?php endforeach;?>
<button type="submit" class="btn btn-outline" style="margin-top:18px"><i data-lucide="refresh-cw"></i> Cập nhật giỏ hàng</button></form>
<?php foreach($items as $p):?><form id="remove-<?= $p['id'] ?>" action="<?= url('cart.php') ?>" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?= $p['id'] ?>"></form><?php endforeach;?></div>
<aside><div class="panel summary-box"><h3>Tóm tắt đơn hàng</h3><div class="summary-row"><span>Tạm tính</span><strong><?= money($subtotal) ?></strong></div><div class="summary-row"><span>Phí giao hàng (tạm tính)</span><strong><?= $shipping===0?'Miễn phí':money($shipping) ?></strong></div><div class="summary-row total"><span>Tổng cộng</span><strong><?= money($subtotal+$shipping) ?></strong></div><a class="btn btn-primary" href="<?= url('checkout.php') ?>">Tiến hành đặt hàng <i data-lucide="arrow-right"></i></a><a class="btn btn-outline" href="<?= url('catalog.php') ?>">Tiếp tục mua sắm</a><div class="payment-note"><i data-lucide="shield-check"></i> Đơn hàng được xác nhận trước khi xử lý.</div></div></aside></div><?php endif;?></main>
<?php require __DIR__.'/app/partials/footer.php';?>
