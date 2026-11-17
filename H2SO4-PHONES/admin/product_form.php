<?php
require __DIR__.'/../app/bootstrap.php';$admin=require_admin();$id=(int)($_GET['id']??0);
$original=$id?one('SELECT * FROM products WHERE id=?',[$id]):null;
if($id&&!$original){http_response_code(404);exit('Sản phẩm không tồn tại');}
$categories=all('SELECT * FROM categories ORDER BY sort_order,id');$errors=[];
$data=$original??['name'=>'','slug'=>'','category_id'=>1,'brand'=>'','sku'=>'','price'=>'','old_price'=>'','stock'=>0,'short_description'=>'','description'=>'','specs'=>'','image'=>null,'is_featured'=>0,'is_active'=>1];
function slugify_name(string $s): string {
 $map=['đ'=>'d','Đ'=>'D'];$s=strtr($s,$map);$text=@iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);$s=strtolower($text?:$s);
 return trim(preg_replace('/[^a-z0-9]+/','-',$s),'-');
}
if(post()){
 check_csrf();$keys=['name','slug','category_id','brand','sku','price','old_price','stock','short_description','description','specs'];
 foreach($keys as $key)$data[$key]=trim((string)($_POST[$key]??''));
 $data['is_featured']=isset($_POST['is_featured'])?1:0;$data['is_active']=isset($_POST['is_active'])?1:0;
 if(!$data['slug'])$data['slug']=substr(slugify_name($data['name']),0,210);
 if(mb_strlen($data['name'])<3||mb_strlen($data['name'])>200)$errors[]='Tên sản phẩm cần 3–200 ký tự.';
 if(!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/',$data['slug'])||strlen($data['slug'])>220)$errors[]='Đường dẫn (slug) chỉ gồm chữ thường, số và dấu gạch ngang.';
 if(mb_strlen($data['brand'])<2||mb_strlen($data['brand'])>100)$errors[]='Thương hiệu không hợp lệ.';
 if(!preg_match('/^[a-zA-Z0-9_-]{3,70}$/',$data['sku']))$errors[]='SKU chỉ dùng chữ, số, gạch ngang hoặc gạch dưới (3–70 ký tự).';
 if(!filter_var($data['price'],FILTER_VALIDATE_INT)||$data['price']<1)$errors[]='Giá bán phải là số nguyên dương.';
 if($data['old_price']!==''&&(!ctype_digit($data['old_price'])||$data['old_price']<0))$errors[]='Giá gốc không hợp lệ.';
 if(!ctype_digit((string)$data['stock'])||(int)$data['stock']>100000)$errors[]='Tồn kho phải từ 0 đến 100000.';
 if(!one('SELECT id FROM categories WHERE id=?',[(int)$data['category_id']]))$errors[]='Danh mục không tồn tại.';
 if(mb_strlen($data['short_description'])>450)$errors[]='Mô tả ngắn tối đa 450 ký tự.';
 if(one('SELECT id FROM products WHERE slug=? AND id<>?',[$data['slug'],$id]))$errors[]='Slug đã tồn tại.';
 if(one('SELECT id FROM products WHERE sku=? AND id<>?',[$data['sku'],$id]))$errors[]='Mã SKU đã tồn tại.';
 if(!$errors){
 try{
  $image=upload_product_image('image',$original['image']??null);
  $values=[(int)$data['category_id'],$data['name'],$data['slug'],$data['brand'],$data['sku'],(int)$data['price'],$data['old_price']===''?null:(int)$data['old_price'],(int)$data['stock'],$image,$data['short_description'],$data['description'],$data['specs'],$data['is_featured'],$data['is_active']];
  if($id){$values[]=$id;query('UPDATE products SET category_id=?,name=?,slug=?,brand=?,sku=?,price=?,old_price=?,stock=?,image=?,short_description=?,description=?,specs=?,is_featured=?,is_active=? WHERE id=?',$values);flash('success','Cập nhật sản phẩm thành công.');}
  else{query('INSERT INTO products(category_id,name,slug,brand,sku,price,old_price,stock,image,short_description,description,specs,is_featured,is_active) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)',$values);flash('success','Thêm sản phẩm mới thành công.');}
  redirect('admin/products.php');
 }catch(Throwable $ex){error_log('Product form: '.$ex->getMessage());$errors[]= $ex instanceof RuntimeException ? $ex->getMessage() : 'Lỗi lưu sản phẩm. Vui lòng kiểm tra dữ liệu.';}
 }
}
$admin_title=$id?'Chỉnh sửa sản phẩm':'Thêm sản phẩm mới';$admin_menu='products';require __DIR__.'/partials/header.php';
?>
<div class="admin-panel admin-form"><a class="section-more" href="<?= url('admin/products.php') ?>" style="margin-bottom:14px"><i data-lucide="arrow-left"></i> Quay lại danh sách</a><?php if($errors):?><div class="error-list"><ul><?php foreach($errors as $e):?><li><?= e($e) ?></li><?php endforeach;?></ul></div><?php endif;?><form method="post" enctype="multipart/form-data"><?= csrf_field() ?><div class="form-row"><div class="form-group"><label>Tên sản phẩm *</label><input id="product-name" required name="name" maxlength="200" value="<?= e($data['name']) ?>"></div><div class="form-group"><label>Đường dẫn (slug)</label><input id="product-slug" name="slug" value="<?= e($data['slug']) ?>" placeholder="tu-dong-tao-tu-ten"></div></div>
<div class="form-row"><div class="form-group"><label>Danh mục *</label><select name="category_id"><?php foreach($categories as $c):?><option value="<?= $c['id'] ?>" <?= $data['category_id']==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach;?></select></div><div class="form-group"><label>Thương hiệu *</label><input name="brand" value="<?= e($data['brand']) ?>" required placeholder="H2SO4, Samsung, Xiaomi..."></div></div>
<div class="form-row"><div class="form-group"><label>Mã SKU *</label><input required name="sku" value="<?= e($data['sku']) ?>" placeholder="H2S-PRO-001"></div><div class="form-group"><label>Tồn kho *</label><input name="stock" type="number" min="0" max="100000" required value="<?= e((string)$data['stock']) ?>"></div></div>
<div class="form-row"><div class="form-group"><label>Giá bán (VNĐ) *</label><input type="number" min="1" step="1" name="price" required value="<?= e((string)$data['price']) ?>"></div><div class="form-group"><label>Giá gốc để so sánh (VNĐ)</label><input type="number" min="0" step="1" name="old_price" value="<?= e((string)$data['old_price']) ?>"></div></div>
<div class="form-group"><label>Mô tả ngắn</label><textarea name="short_description" maxlength="450"><?= e($data['short_description']) ?></textarea></div><div class="form-group"><label>Mô tả chi tiết</label><textarea name="description" rows="5"><?= e($data['description']) ?></textarea></div><div class="form-group"><label>Thông số kỹ thuật</label><input name="specs" value="<?= e($data['specs']) ?>" placeholder="6.7 inch | 256GB | 5000 mAh"></div><div class="form-group"><label>Hình ảnh (JPG/PNG/WebP, tối đa 3MB)</label><input type="file" name="image" accept="image/png,image/jpeg,image/webp"><?php if($data['image']):?><img src="<?= e(product_image($data['image'])) ?>" style="width:140px;border-radius:10px;margin-top:12px" alt="Ảnh hiện tại"><?php endif;?></div>
<div style="display:flex;gap:28px;margin:18px 0"><label><input type="checkbox" name="is_featured" <?= $data['is_featured']?'checked':'' ?>> Sản phẩm nổi bật</label><label><input type="checkbox" name="is_active" <?= $data['is_active']?'checked':'' ?>> Hiển thị công khai</label></div><button type="submit" class="btn btn-primary"><i data-lucide="save"></i> <?= $id?'Lưu thay đổi':'Thêm sản phẩm' ?></button></form></div>
<script>const n=document.getElementById('product-name'),s=document.getElementById('product-slug');n.addEventListener('input',()=>{if(s.dataset.edited==='true')return;s.value=n.value.normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/đ/g,'d').replace(/Đ/g,'D').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');});s.addEventListener('input',()=>s.dataset.edited='true');<?php if($id):?>s.dataset.edited='true';<?php endif;?></script>
<?php require __DIR__.'/partials/footer.php';?>
