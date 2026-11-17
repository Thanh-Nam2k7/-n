<?php
$page_title = $page_title ?? 'Khám phá công nghệ | H2SO4 PHONES';
$active_tab = $active_tab ?? '';
$me = user();
$categories = nav_categories();
?>
<!doctype html>
<html lang="vi"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#10151e">
<meta name="description" content="H2SO4 PHONES - Cửa hàng công nghệ với trải nghiệm mua sắm hiện đại. Website demo đồ án PHP Core.">
<title><?= e($page_title) ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<link rel="icon" type="image/svg+xml" href="<?= url('assets/images/logo.svg') ?>">
</head><body>
<div class="topbar"><div class="container topbar-content"><span><i data-lucide="zap"></i> THIẾT BỊ CHẤT - DEAL CHẤT HƠN</span><span class="topbar-mid">Miễn phí giao hàng với đơn từ 3.000.000₫</span><span><i data-lucide="shield-check"></i> MUA SẮM AN TOÀN</span></div></div>
<header class="site-header"><div class="container header-row">
<a class="brand" href="<?= url('index.php') ?>" aria-label="H2SO4 Phones trang chủ"><span class="brand-icon"><i data-lucide="atom"></i></span><span class="brand-name">H<span>2</span>SO<span>4</span><small>PHONES<span class="brand-dot">.</span></small></span></a>
<form class="header-search" action="<?= url('catalog.php') ?>" method="get"><i data-lucide="search"></i><input name="q" type="search" placeholder="Bạn muốn tìm điện thoại, laptop, phụ kiện..." value="<?= e((string)($_GET['q'] ?? '')) ?>" aria-label="Tìm kiếm sản phẩm"><button type="submit">Tìm kiếm</button></form>
<div class="header-actions">
<a href="<?= url('lookup.php') ?>" class="header-action"><i data-lucide="package-search"></i><span>Tra cứu<small>đơn hàng</small></span></a>
<a href="<?= url($me ? ($me['role'] === 'admin' ? 'admin/index.php' : 'account.php') : 'login.php') ?>" class="header-action"><i data-lucide="user-round"></i><span><?= $me ? e(cut($me['name'], 13)) : 'Tài khoản' ?><small><?= $me ? ($me['role'] === 'admin' ? 'Quản trị' : 'Của tôi') : 'Đăng nhập' ?></small></span></a>
<a href="<?= url('cart.php') ?>" class="header-action cart-action"><i data-lucide="shopping-bag"></i><span>Giỏ hàng<small>Sản phẩm</small></span><b class="cart-bubble"><?= cart_count() ?></b></a>
</div>
<button id="mobile-menu-toggle" class="mobile-menu-toggle" type="button" aria-label="Mở danh mục"><i data-lucide="menu"></i></button>
</div>
<nav class="main-nav"><div class="container nav-row">
<div class="nav-dropdown"><button type="button" class="nav-pill"><i data-lucide="layout-grid"></i> Danh mục sản phẩm <i data-lucide="chevron-down"></i></button><div class="dropdown-panel">
<?php foreach ($categories as $cat): ?><a href="<?= url('catalog.php?category=' . urlencode($cat['slug'])) ?>"><i data-lucide="<?= e($cat['icon']) ?>"></i><?= e($cat['name']) ?><i class="drop-arrow" data-lucide="chevron-right"></i></a><?php endforeach; ?>
</div></div>
<a href="<?= url('index.php') ?>" class="nav-link <?= $active_tab==='home'?'selected':'' ?>">Trang chủ</a>
<?php foreach (array_slice($categories, 0, 6) as $cat): ?><a href="<?= url('catalog.php?category=' . urlencode($cat['slug'])) ?>" class="nav-link"><?= e($cat['name']) ?></a><?php endforeach; ?>
<a href="<?= url('catalog.php?sort=discount') ?>" class="nav-link nav-sale"><i data-lucide="flame"></i> Giá tốt</a>
</div></nav></header>
<div id="mobile-menu" class="mobile-menu"><div class="container"><a href="<?= url('index.php') ?>">Trang chủ</a><?php foreach ($categories as $cat): ?><a href="<?= url('catalog.php?category=' . urlencode($cat['slug'])) ?>"><?= e($cat['name']) ?></a><?php endforeach; ?><a href="<?= url('admin/index.php') ?>">Quản trị (Admin)</a></div></div>
<?php foreach (flash_messages() as $msg): ?><div class="container"><div class="alert alert-<?= $msg['type']==='success'?'success':'error' ?>"><i data-lucide="<?= $msg['type']==='success'?'check-circle-2':'alert-circle' ?>"></i><?= e($msg['message']) ?></div></div><?php endforeach; ?>
