<?php require_once 'layout.php'; require_auth(); if(is_admin()) redirect(BASE_URL.'/admin.php');
if(isset($_GET['remove'])){db()->prepare('DELETE FROM favorites WHERE user_id=? AND product_id=?')->execute([$_SESSION['user_id'],(int)$_GET['remove']]);redirect(BASE_URL.'/favorites.php');}
$s=db()->prepare('SELECT p.*,(SELECT image_path FROM product_images WHERE product_id=p.id LIMIT 1) img FROM favorites f JOIN products p ON p.id=f.product_id WHERE f.user_id=?');$s->execute([$_SESSION['user_id']]);$items=$s->fetchAll();page_header('Favorilerim'); ?>
<h1>Favorilerim</h1><div class="grid"><?php foreach($items as $p): ?><div class="card product"><a href="product.php?id=<?=$p['id']?>"><img src="<?=UPLOAD_URL.'/'.h($p['img']?:'logo.jpg')?>"><h3><?=h($p['name'])?></h3><p class="price"><?=money($p['price'])?></p></a><a class="btn danger" href="?remove=<?=$p['id']?>">Favoriden çıkar</a></div><?php endforeach; ?></div>
<?php page_footer(); ?>
