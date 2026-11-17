<?php
require __DIR__.'/../app/bootstrap.php';require_admin();if(!post()){http_response_code(405);exit('Method not allowed');}check_csrf();$id=(int)($_POST['id']??0);$a=(string)($_POST['action']??'');
if($a==='toggle'){query('UPDATE categories SET is_active=1-is_active WHERE id=?',[$id]);flash('success','Cập nhật trạng thái danh mục.');}
elseif($a==='delete'){$count=(int)(one('SELECT COUNT(*) n FROM products WHERE category_id=?',[$id])['n']??0);if($count){flash('error','Danh mục đang có '.$count.' sản phẩm, không thể xóa. Bạn có thể ẩn danh mục.');}else{query('DELETE FROM categories WHERE id=?',[$id]);flash('success','Đã xóa danh mục.');}}
redirect('admin/categories.php');
