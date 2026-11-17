<?php
require __DIR__.'/app/bootstrap.php';
if(!post()){http_response_code(405);exit('Method not allowed');}
check_csrf();unset($_SESSION['user_id']);session_regenerate_id(true);flash('success','Đã đăng xuất.');redirect('index.php');
