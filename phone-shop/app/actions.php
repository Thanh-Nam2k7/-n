<?php
if($_SERVER['REQUEST_METHOD']!=='POST')return;
check_csrf();
$action=input('action');
try {
 if($action==='register'){
  $name=input('name');$email=strtolower(input('email'));$pass=input('password');
  if($name===''||strlen($name)>100||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>190||strlen($pass)<8||strlen($pass)>72)throw new DomainException('Tên tối đa 100 byte; email hợp lệ; mật khẩu từ 8–72 byte.');
  try {query('INSERT INTO users(name,email,password) VALUES(?,?,?)',[$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);}catch(PDOException $ex){if($ex->getCode()==='23000')throw new DomainException('Email đã được sử dụng.');throw $ex;}
  flash('Đăng ký thành công. Mời bạn đăng nhập.');go('index.php?page=login');
 }
 if($action==='login'){
  // Giới hạn thử đăng nhập theo email trong database, không chỉ theo cookie.
  $email=strtolower(input('email'));$key=hash('sha256',$email);
  query('INSERT IGNORE INTO login_attempts(email_key,attempts,window_start) VALUES(?,0,NOW())',[$key]);
  query('UPDATE login_attempts SET attempts=0,window_start=NOW() WHERE email_key=? AND window_start < DATE_SUB(NOW(), INTERVAL 15 MINUTE)',[$key]);
  $s=query('UPDATE login_attempts SET attempts=attempts+1 WHERE email_key=? AND attempts<10',[$key]);
  if(!$s->rowCount())throw new DomainException('Quá nhiều lần thử. Vui lòng thử lại sau 15 phút.');
  $u=query('SELECT * FROM users WHERE email=?',[$email])->fetch();
  if(!$u||!password_verify(input('password'),$u['password']))throw new DomainException('Email hoặc mật khẩu không đúng.');
  query('DELETE FROM login_attempts WHERE email_key=?',[$key]);session_regenerate_id(true);$_SESSION['uid']=$u['id'];$_SESSION['csrf']=bin2hex(random_bytes(32));go('index.php');
 }
 if($action==='logout'){$_SESSION=[];session_regenerate_id(true);go('index.php');}
 if($action==='cart_add'){
  $id=integer(input('id'),1);$qty=integer(input('qty'),1,99);$p=query('SELECT * FROM products WHERE id=? AND active=1',[$id])->fetch();
  $new=($_SESSION['cart'][$id]??0)+$qty;
  if(!$p||$new>$p['stock']||$new>99)throw new DomainException('Sản phẩm không còn đủ số lượng (tối đa 99 mỗi loại).');
  $_SESSION['cart'][$id]=$new;flash('Đã thêm sản phẩm.');go('index.php?page=cart');
 }
 if($action==='cart_update'){
  $id=integer(input('id'),1);$qty=integer(input('qty'),0,99);
  if($qty===0)unset($_SESSION['cart'][$id]);else{
   $p=query('SELECT * FROM products WHERE id=? AND active=1',[$id])->fetch();
   if(!$p||$qty>$p['stock'])throw new DomainException('Không đủ hàng.');$_SESSION['cart'][$id]=$qty;
  }go('index.php?page=cart');
 }
 if($action==='checkout'){
  auth();$name=input('name');$phone=input('phone');$address=input('address');
  if($name===''||strlen($name)>100||!preg_match('/^[0-9+ .-]{9,20}$/',$phone)||strlen($address)<10||strlen($address)>500)throw new DomainException('Kiểm tra tên, số điện thoại và địa chỉ (10–500 byte).');
  $cart=$_SESSION['cart']??[];if(!$cart)throw new DomainException('Giỏ hàng trống.');ksort($cart);
  $db->beginTransaction();$items=[];$total=0;
  foreach($cart as $id=>$qty){
   $p=query('SELECT * FROM products WHERE id=? FOR UPDATE',[$id])->fetch();
   $qty=integer($qty,1,99);
   if(!$p||!$p['active']||$p['stock']<$qty)throw new DomainException('Một sản phẩm đã hết hoặc thiếu hàng. Hãy cập nhật giỏ hàng.');
   $total+=(int)$p['price']*$qty;$items[]=[$p,$qty];
  }
  query('INSERT INTO orders(user_id,receiver,phone,address,total) VALUES(?,?,?,?,?)',[user()['id'],$name,$phone,$address,$total]);$oid=$db->lastInsertId();
  foreach($items as [$p,$qty]){
   query('INSERT INTO order_items(order_id,product_id,product_name,price,quantity) VALUES(?,?,?,?,?)',[$oid,$p['id'],$p['name'],$p['price'],$qty]);
   query('UPDATE products SET stock=stock-? WHERE id=?',[$qty,$p['id']]);
  }
  $db->commit();unset($_SESSION['cart']);flash('Đặt hàng thành công. Thanh toán khi nhận hàng (COD).');go('index.php?page=orders');
 }
 if(strpos($action,'admin_')===0){
  auth(true);
  if($action==='admin_category_save'){
   $name=input('name');if($name===''||strlen($name)>100)throw new DomainException('Tên danh mục không hợp lệ.');$id=integer(input('id'));
   if($id)query('UPDATE categories SET name=? WHERE id=?',[$name,$id]);else query('INSERT INTO categories(name) VALUES(?)',[$name]);
  }elseif($action==='admin_category_delete'){
   $id=integer(input('id'),1);if(query('SELECT id FROM products WHERE category_id=? LIMIT 1',[$id])->fetch())throw new DomainException('Danh mục còn sản phẩm. Hãy chuyển hoặc xóa sản phẩm trước.');query('DELETE FROM categories WHERE id=?',[$id]);
  }elseif($action==='admin_product_save'){
   $id=integer(input('id'));$name=input('name');$model=input('compatible');$desc=input('description');$img=input('image');
   if($name===''||strlen($name)>190||strlen($model)>190||strlen($desc)>10000)throw new DomainException('Tên, dòng máy hoặc mô tả quá dài / còn trống.');
   // Chỉ ảnh có sẵn trong assets; không nhận URL hoặc đường dẫn tùy ý.
   if(!in_array($img,['case.svg','charger.svg','cable.svg','audio.svg','stand.svg','glass.svg'],true))throw new DomainException('Ảnh không hợp lệ.');
   $category=integer(input('category_id'),1);if(!query('SELECT id FROM categories WHERE id=?',[$category])->fetch())throw new DomainException('Danh mục không tồn tại.');
   $args=[$name,$category,integer(input('price'),1000,100000000),integer(input('stock'),0,100000),$model,$desc,$img,isset($_POST['active'])?1:0];
   if($id){$args[]=$id;query('UPDATE products SET name=?,category_id=?,price=?,stock=?,compatible=?,description=?,image=?,active=? WHERE id=?',$args);}else query('INSERT INTO products(name,category_id,price,stock,compatible,description,image,active) VALUES(?,?,?,?,?,?,?,?)',$args);
  }elseif($action==='admin_product_delete'){
   $id=integer(input('id'),1);
   if(query('SELECT id FROM order_items WHERE product_id=? LIMIT 1',[$id])->fetch())query('UPDATE products SET active=0 WHERE id=?',[$id]);else query('DELETE FROM products WHERE id=?',[$id]);
  }elseif($action==='admin_status'){
   $id=integer(input('id'),1);$next=input('status');$db->beginTransaction();$o=query('SELECT * FROM orders WHERE id=? FOR UPDATE',[$id])->fetch();
   $allowed=['pending'=>['confirmed','cancelled'],'confirmed'=>['shipping','cancelled'],'shipping'=>['completed'],'completed'=>[],'cancelled'=>[]];
   if(!$o||!in_array($next,$allowed[$o['status']],true))throw new DomainException('Chuyển trạng thái không hợp lệ.');
   if($next==='cancelled')foreach(query('SELECT * FROM order_items WHERE order_id=? ORDER BY product_id',[$id])->fetchAll() as $item)query('UPDATE products SET stock=stock+? WHERE id=?',[$item['quantity'],$item['product_id']]);
   query('UPDATE orders SET status=? WHERE id=?',[$next,$id]);$db->commit();
  }else throw new DomainException('Thao tác không hợp lệ.');
  flash('Đã lưu thay đổi.');go('admin.php');
 }
 throw new DomainException('Thao tác không hợp lệ.');
}catch(DomainException $ex){if($db->inTransaction())$db->rollBack();$error=$ex->getMessage();}
catch(PDOException $ex){if($db->inTransaction())$db->rollBack();error_log((string)$ex);$error='Không thể lưu. Dữ liệu có thể trùng hoặc vừa thay đổi. Vui lòng thử lại.';}
