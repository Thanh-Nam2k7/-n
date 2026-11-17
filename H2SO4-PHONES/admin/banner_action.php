<?php
require __DIR__.'/../app/bootstrap.php';require_admin();if(!post()){http_response_code(405);exit('Method not allowed');}check_csrf();$id=(int)($_POST['id']??0);$action=(string)($_POST['action']??'');
if($action==='toggle'){query('UPDATE banners SET is_active=1-is_active WHERE id=?',[$id]);flash('success','Đã cập nhật hiển thị banner.');}
elseif($action==='delete'){query('DELETE FROM banners WHERE id=?',[$id]);flash('success','Đã xóa banner.');}
redirect('admin/banners.php');
