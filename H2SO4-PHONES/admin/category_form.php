<?php
require __DIR__.'/../app/bootstrap.php';$admin=require_admin();$id=(int)($_GET['id']??0);$original=$id?one('SELECT * FROM categories WHERE id=?',[$id]):null;if($id&&!$original){http_response_code(404);exit('Không tìm thấy danh mục.');}
$d=$original??['name'=>'','slug'=>'','icon'=>'smartphone','description'=>'','sort_order'=>0,'is_active'=>1];$errors=[];
if(post()){
 check_csrf();foreach(['name','slug','icon','description','sort_order'] as $key)$d[$key]=trim((string)($_POST[$key]??''));$d['is_active']=isset($_POST['is_active'])?1:0;
 if(mb_strlen($d['name'])<2||mb_strlen($d['name'])>100)$errors[]='Tên danh mục cần 2–100 ký tự.';
 if(!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/',$d['slug']))$errors[]='Slug chỉ gồm chữ thường, số và dấu gạch ngang.';
 if(!preg_match('/^[a-z0-9-]{2,32}$/',$d['icon']))$errors[]='Icon cần là tên biểu tượng Lucide.';
 if(mb_strlen($d['description'])>255)$errors[]='Mô tả tối đa 255 ký tự.';
 if(one('SELECT id FROM categories WHERE slug=? AND id<>?',[$d['slug'],$id]))$errors[]='Slug đã tồn tại.';
 if(!$errors){try{if($id)query('UPDATE categories SET name=?,slug=?,icon=?,description=?,sort_order=?,is_active=? WHERE id=?',[$d['name'],$d['slug'],$d['icon'],$d['description'],(int)$d['sort_order'],$d['is_active'],$id]);else query('INSERT INTO categories(name,slug,icon,description,sort_order,is_active) VALUES(?,?,?,?,?,?)',[$d['name'],$d['slug'],$d['icon'],$d['description'],(int)$d['sort_order'],$d['is_active']]);flash('success','Đã lưu danh mục.');redirect('admin/categories.php');}catch(PDOException $ex){$errors[]='Không thể lưu danh mục.';}}
}
$admin_title=$id?'Sửa danh mục':'Thêm danh mục';$admin_menu='categories';require __DIR__.'/partials/header.php';
?>
<div class="admin-panel admin-form"><a class="section-more" href="<?= url('admin/categories.php') ?>">← Quay lại danh mục</a><?php if($errors):?><div class="error-list"><?= e(implode(' ',$errors)) ?></div><?php endif;?><form method="post"><?= csrf_field() ?><div class="form-row"><div class="form-group"><label>Tên danh mục</label><input required name="name" value="<?= e($d['name']) ?>"></div><div class="form-group"><label>Slug (không dấu)</label><input required name="slug" value="<?= e($d['slug']) ?>" placeholder="phu-kien"></div></div><div class="form-row"><div class="form-group"><label>Icon Lucide</label><input name="icon" value="<?= e($d['icon']) ?>" placeholder="smartphone"></div><div class="form-group"><label>Thứ tự hiển thị</label><input type="number" name="sort_order" value="<?= (int)$d['sort_order'] ?>"></div></div><div class="form-group"><label>Mô tả</label><input name="description" value="<?= e($d['description']) ?>"></div><label><input type="checkbox" name="is_active" <?= $d['is_active']?'checked':'' ?>> Hiển thị danh mục</label><div style="margin-top:24px"><button class="btn btn-primary" type="submit">Lưu danh mục</button></div></form></div>
<?php require __DIR__.'/partials/footer.php';?>
