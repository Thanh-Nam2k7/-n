<?php
require __DIR__ . '/app/bootstrap.php';
$category = trim((string)($_GET['category'] ?? ''));
$cat = $category ? one('SELECT * FROM categories WHERE slug=? AND is_active=1', [$category]) : null;
if ($category && !$cat) { http_response_code(404); exit('Danh mục không tồn tại.'); }
$q = trim((string)($_GET['q'] ?? '')); $brand = trim((string)($_GET['brand'] ?? ''));
$min = max(0, (int)($_GET['min'] ?? 0)); $max = max(0, (int)($_GET['max'] ?? 0));
$sort = (string)($_GET['sort'] ?? 'newest');
$sorting = ['newest'=>'p.id DESC','price_asc'=>'p.price ASC','price_desc'=>'p.price DESC','discount'=>'(COALESCE(p.old_price, p.price)-p.price) DESC, p.id DESC','name'=>'p.name ASC'];
if (!isset($sorting[$sort])) $sort='newest';
$page = max(1, min(10000, (int)($_GET['page'] ?? 1))); $perPage=12;
$where = ['p.is_active=1','c.is_active=1'];$params=[];
if($cat){$where[]='p.category_id=?';$params[]=$cat['id'];}
if($q!==''){$where[]='(p.name LIKE ? OR p.brand LIKE ? OR p.sku LIKE ?)';$wild='%'.str_replace(['%','_'],['\\%','\\_'],$q).'%';array_push($params,$wild,$wild,$wild);}
if($brand!==''){$where[]='p.brand=?';$params[]=$brand;}
if($min>0){$where[]='p.price>=?';$params[]=$min;}
if($max>0){$where[]='p.price<=?';$params[]=$max;}
$filter = implode(' AND ', $where);
$total=(int)(one('SELECT COUNT(*) AS cnt FROM products p JOIN categories c ON p.category_id=c.id WHERE '.$filter, $params)['cnt'] ?? 0);
$pages=max(1,(int)ceil($total/$perPage));$page=min($page,$pages);$offset=($page-1)*$perPage;
$products=all('SELECT p.* FROM products p JOIN categories c ON p.category_id=c.id WHERE '.$filter.' ORDER BY '.$sorting[$sort].' LIMIT '.$perPage.' OFFSET '.$offset,$params);
$brandSQL='SELECT DISTINCT p.brand FROM products p JOIN categories c ON c.id=p.category_id WHERE p.is_active=1 AND c.is_active=1'.($cat?' AND p.category_id='.(int)$cat['id']:'').' ORDER BY p.brand';
$brands=all($brandSQL);
function catalog_link(array $updates=[]): string { $args=array_merge($_GET,$updates);foreach($args as $k=>$v)if($v===null||$v==='')unset($args[$k]);return url('catalog.php').'?'.http_build_query($args); }
$page_title=($cat ? $cat['name'] : ($q ? 'Tìm kiếm: '.$q : 'Tất cả sản phẩm')).' | H2SO4 PHONES';
require __DIR__ . '/app/partials/header.php';
?>
<main class="container"><div class="breadcrumb"><a href="<?= url('index.php') ?>">Trang chủ</a><i data-lucide="chevron-right"></i><span><?= e($cat['name']??'Sản phẩm') ?></span></div>
<h1 class="page-title"><?= $q?'Kết quả cho “'.e($q).'”':e($cat['name']??'Khám phá tất cả sản phẩm') ?></h1><p class="page-subtitle"><?= e($cat['description']??'Bộ sưu tập công nghệ phong phú, dễ tìm kiếm và so sánh.') ?></p>
<div class="filter-chips"><a class="chip <?= !$category?'active':'' ?>" href="<?= url('catalog.php') ?>">Tất cả</a><?php foreach ($categories as $c): ?><a class="chip <?= $category===$c['slug']?'active':'' ?>" href="<?= url('catalog.php?category='.urlencode($c['slug'])) ?>"><?= e($c['name']) ?></a><?php endforeach; ?></div>
<div class="catalog-layout"><aside class="panel filter-panel"><h3><i data-lucide="sliders-horizontal"></i> Chọn theo tiêu chí</h3><form action="<?= url('catalog.php') ?>" method="get">
<?php if($category):?><input type="hidden" name="category" value="<?= e($category) ?>"><?php endif;?><?php if($q):?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif;?>
<div class="filter-group"><h4>Thương hiệu</h4><label class="filter-option"><input type="radio" name="brand" value="" <?= $brand===''?'checked':'' ?>> Tất cả thương hiệu</label><?php foreach($brands as $b):?><label class="filter-option"><input type="radio" name="brand" value="<?= e($b['brand']) ?>" <?= $brand===$b['brand']?'checked':'' ?>><?= e($b['brand']) ?></label><?php endforeach;?></div>
<div class="filter-group"><h4>Khoảng giá (VNĐ)</h4><div class="range-inputs"><input class="input" type="number" name="min" min="0" step="10000" placeholder="Từ" value="<?= $min?:'' ?>"><span>–</span><input class="input" type="number" name="max" min="0" step="10000" placeholder="Đến" value="<?= $max?:'' ?>"></div></div>
<button class="btn btn-primary filter-apply" type="submit"><i data-lucide="filter"></i> Áp dụng bộ lọc</button><a class="filter-reset" href="<?= url('catalog.php'.($category?'?category='.urlencode($category):'')) ?>">Xóa bộ lọc</a></form></aside>
<div class="catalog-results"><div class="catalog-toolbar"><strong>Tìm thấy <span style="color:var(--mint)"><?= $total ?></span> sản phẩm</strong><form action="<?= url('catalog.php') ?>" method="get" data-auto-submit><?php foreach($_GET as $key=>$value):if($key==='sort'||$key==='page'||!is_scalar($value))continue;?><input type="hidden" name="<?= e($key) ?>" value="<?= e((string)$value) ?>"><?php endforeach;?><select name="sort" aria-label="Sắp xếp"><option value="newest" <?= $sort==='newest'?'selected':'' ?>>Mới nhất</option><option value="discount" <?= $sort==='discount'?'selected':'' ?>>Ưu đãi tốt</option><option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Giá thấp đến cao</option><option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Giá cao đến thấp</option><option value="name" <?= $sort==='name'?'selected':'' ?>>Tên A–Z</option></select></form></div>
<?php if(!$products):?><div class="empty-state"><i data-lucide="package-search"></i><h3>Chưa tìm thấy sản phẩm</h3><p>Thử chọn khoảng giá hoặc thương hiệu khác nhé.</p><a href="<?= url('catalog.php') ?>" class="btn btn-primary">Xem tất cả</a></div><?php else:?><div class="product-grid"><?php foreach($products as $p) require __DIR__.'/app/partials/card.php'; ?></div><?php endif;?>
<?php if($pages>1):?><div class="pagination"><?php for($i=max(1,$page-2);$i<=min($pages,$page+2);$i++):?><?php if($i===$page):?><span class="current"><?= $i ?></span><?php else:?><a href="<?= e(catalog_link(['page'=>$i])) ?>"><?= $i ?></a><?php endif;?><?php endfor;?></div><?php endif;?></div></div></main>
<?php require __DIR__.'/app/partials/footer.php';?>
