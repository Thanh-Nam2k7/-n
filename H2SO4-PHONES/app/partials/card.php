<?php // Sử dụng biến $p là một hàng từ bảng products. ?>
<article class="product-card">
<a class="product-photo" href="<?= url('product.php?slug=' . urlencode($p['slug'])) ?>">
<?php if (!empty($p['old_price']) && (int)$p['old_price'] > (int)$p['price']): ?><span class="sale-badge">-<?= round((1 - (int)$p['price'] / (int)$p['old_price']) * 100) ?>%</span><?php endif; ?>
<img src="<?= e(product_image($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy"></a>
<div class="product-content"><div class="product-brand"><?= e($p['brand']) ?> · <span><?= (int)$p['stock']>0?'Còn hàng':'Hết hàng' ?></span></div>
<a class="product-title" href="<?= url('product.php?slug=' . urlencode($p['slug'])) ?>"><?= e($p['name']) ?></a>
<div class="price-line"><strong><?= money($p['price']) ?></strong><?php if (!empty($p['old_price']) && (int)$p['old_price'] > (int)$p['price']): ?><s><?= money($p['old_price']) ?></s><?php endif; ?></div>
<div class="card-promo"><i data-lucide="gift"></i> Quà tặng cho thành viên</div>
<div class="card-bottom"><span class="product-rating"><i data-lucide="star"></i> Thiết bị nổi bật</span>
<form method="post" action="<?= url('cart.php') ?>"><?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="qty" value="1"><button type="submit" class="mini-add" <?= (int)$p['stock']<1?'disabled':'' ?> aria-label="Thêm <?= e($p['name']) ?> vào giỏ"><i data-lucide="plus"></i></button></form></div></div>
</article>
