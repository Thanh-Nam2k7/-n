<?php
require __DIR__.'/app/bootstrap.php';$me=require_login();
if(post()){check_csrf();$id=(int)($_POST['id']??0);$p=one('SELECT id,slug FROM products WHERE id=? AND is_active=1',[$id]);
 if($p){$existing=one('SELECT id FROM wishlists WHERE user_id=? AND product_id=?',[$me['id'],$id]);if($existing){query('DELETE FROM wishlists WHERE id=?',[$existing['id']]);flash('success','Đã bỏ khỏi danh sách yêu thích.');}else{query('INSERT IGNORE INTO wishlists(user_id,product_id) VALUES(?,?)',[$me['id'],$id]);flash('success','Đã thêm vào danh sách yêu thích.');}redirect('product.php?slug='.urlencode($p['slug']));}
 redirect('wishlist.php');
}
$products=all('SELECT p.* FROM products p INNER JOIN wishlists w ON w.product_id=p.id WHERE w.user_id=? AND p.is_active=1 ORDER BY w.created_at DESC',[$me['id']]);
$section='wishlist';$page_title='Yêu thích | H2SO4 PHONES';require __DIR__.'/app/partials/header.php';
?>
<main class="container"><div class="breadcrumb"><a href="<?= url('index.php') ?>">Trang chủ</a><i data-lucide="chevron-right"></i>Yêu thích</div><h1 class="page-title">Sản phẩm <span style="color:var(--mint)">yêu thích</span></h1><div class="account-layout"><?php require __DIR__.'/app/partials/account_nav.php';?><div><?php if($products):?><div class="product-grid" style="grid-template-columns:repeat(auto-fill,minmax(175px,1fr))"><?php foreach($products as $p) require __DIR__.'/app/partials/card.php';?></div><?php else:?><div class="empty-state"><i data-lucide="heart"></i><h3>Chưa có sản phẩm yêu thích</h3><p>Ghé trang sản phẩm và nhấn nút yêu thích nhé!</p><a class="btn btn-primary" href="<?= url('catalog.php') ?>">Xem sản phẩm</a></div><?php endif;?></div></div></main>
<?php require __DIR__.'/app/partials/footer.php';?>
