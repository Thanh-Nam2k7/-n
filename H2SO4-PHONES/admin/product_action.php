<?php
require __DIR__.'/../app/bootstrap.php';require_admin();if(!post()){http_response_code(405);exit('Method not allowed');}check_csrf();
$id=(int)($_POST['id']??0);$action=(string)($_POST['action']??'');$p=one('SELECT id,name,is_active FROM products WHERE id=?',[$id]);
if(!$p){flash('error','Không tìm thấy sản phẩm.');redirect('admin/products.php');}
if($action==='toggle'){query('UPDATE products SET is_active=1-is_active WHERE id=?',[$id]);flash('success','Đã thay đổi trạng thái sản phẩm.');}
elseif($action==='delete'){query('DELETE FROM products WHERE id=?',[$id]);flash('success','Đã xóa sản phẩm. Lịch sử đơn hàng vẫn được lưu.');}
redirect('admin/products.php');
