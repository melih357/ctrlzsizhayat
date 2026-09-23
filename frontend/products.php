<?php require_once 'layout.php'; require_auth(); if(is_admin()) redirect(BASE_URL.'/admin.php');
$cats=db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$where=[];$params=[];
if(!empty($_GET['cat'])){$where[]='p.category_id=?';$params[]=$_GET['cat'];}
if(isset($_GET['min'])&&$_GET['min']!==''){$where[]='p.price>=?';$params[]=(float)$_GET['min'];}
if(isset($_GET['max'])&&$_GET['max']!==''){$where[]='p.price<=?';$params[]=(float)$_GET['max'];}
if(!empty($_GET['q'])){$where[]='(p.name LIKE ? OR p.description LIKE ?)';$params[]='%'.$_GET['q'].'%';$params[]='%'.$_GET['q'].'%';}
$order='p.created_at DESC'; if(($_GET['sort']??'')==='asc')$order='p.price ASC'; if(($_GET['sort']??'')==='desc')$order='p.price DESC'; if(($_GET['sort']??'')==='stock')$order='p.stock DESC';
$sql='SELECT p.*,c.name cat,(SELECT image_path FROM product_images WHERE product_id=p.id ORDER BY id LIMIT 1) img FROM products p JOIN categories c ON c.id=p.category_id '.($where?'WHERE '.implode(' AND ',$where):'').' ORDER BY '.$order;
$s=db()->prepare($sql);$s->execute($params);$products=$s->fetchAll(); page_header('Ürünler'); ?>
<h1>Ürünler</h1><form class="card filters" method="get"><input name="q" placeholder="Ürün ara" value="<?=h($_GET['q']??'')?>"><select name="cat"><option value="">Tüm Kategoriler</option><?php foreach($cats as $c): ?><option value="<?=$c['id']?>" <?=($_GET['cat']??'')==$c['id']?'selected':''?>><?=h($c['name'])?></option><?php endforeach; ?></select><input type="number" name="min" min="0" step="0.01" placeholder="Min fiyat" value="<?=h($_GET['min']??'')?>"><input type="number" name="max" min="0" step="0.01" placeholder="Max fiyat" value="<?=h($_GET['max']??'')?>"><select name="sort"><option value="">Yeni ürünler</option><option value="asc" <?=($_GET['sort']??'')==='asc'?'selected':''?>>Ucuzdan pahalıya</option><option value="desc" <?=($_GET['sort']??'')==='desc'?'selected':''?>>Pahalıdan ucuza</option><option value="stock" <?=($_GET['sort']??'')==='stock'?'selected':''?>>Stok çoktan aza</option></select><button class="btn primary">Filtrele</button><a class="btn" href="products.php">Sıfırla</a></form>
<div class="grid"><?php foreach($products as $p): ?><a class="card product" href="product.php?id=<?=$p['id']?>"><img src="<?=UPLOAD_URL.'/'.h($p['img']?:'logo.jpg')?>"><span class="pill"><?=h($p['cat'])?></span><h3><?=h($p['name'])?></h3><p class="muted"><?=h(mb_strimwidth($p['description'],0,70,'...'))?></p><p class="price"><?=money($p['price'])?></p><p>Stok: <?=h($p['stock'])?></p></a><?php endforeach; ?></div>
<?php page_footer(); ?>
