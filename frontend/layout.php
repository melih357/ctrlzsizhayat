<?php require_once __DIR__.'/../backend/config.php'; ?>
<?php
function lang_link($target){ $sep=str_contains($target,'?')?'&':'?'; return $target.$sep.'lang='.(get_lang()==='tr'?'en':'tr'); }
function page_header($title='CTRLZSIZ HAYAT'){ $u=current_user(); $lang=get_lang(); ?>
<!doctype html><html lang="<?=$lang?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($title)?></title><link rel="stylesheet" href="assets/style.css"></head>
<body>
<header class="topbar premium-nav">
  <a class="brand" href="index.php"><img src="assets/logo.jpg" alt="logo"><span>CTRLZSIZ HAYAT</span><small>VIP Store</small></a>
  <?php if($u && !is_admin()): ?>
    <form class="top-search" action="products.php" method="get"><input name="q" placeholder="<?=t('Ürün, kategori veya tarz ara...','Search product, category or style...')?>" value="<?=h($_GET['q']??'')?>"><button>⌕</button></form>
  <?php endif; ?>
  <div class="top-actions">
    <?php if($u && !is_admin()): ?>
      <a class="lang" href="<?=h(basename($_SERVER['PHP_SELF']).'?lang='.($lang==='tr'?'en':'tr'))?>"><?=$lang==='tr'?'EN':'TR'?></a>
      <button class="hamb" onclick="toggleMenu()">☰</button><nav id="menu">
        <a href="index.php"><?=t('Ana Sayfa','Home')?></a><a href="products.php"><?=t('Ürünler','Products')?></a><a href="favorites.php"><?=t('Favorilerim','Favorites')?></a><a href="cart.php"><?=t('Sepetim','Cart')?></a><a href="profile.php"><?=t('Profilim','Profile')?></a><a href="about.php"><?=t('Hakkımızda','About')?></a><a href="contact.php"><?=t('İletişim','Contact')?></a><a href="logout.php"><?=t('Çıkış','Logout')?></a>
      </nav>
    <?php elseif($u && is_admin()): ?><nav><a href="admin.php">Admin Paneli</a><a href="google_settings.php">Google Ayarları</a><a href="logout.php">Çıkış</a></nav><?php else: ?>
      <a class="lang" href="<?=h(lang_link(basename($_SERVER['PHP_SELF'])))?>"><?=$lang==='tr'?'EN':'TR'?></a>
    <?php endif; ?>
  </div>
</header><main class="container"><?=flash()?>
<?php } function page_footer(){ ?>
</main><footer class="foot"><b>CTRLZSIZ HAYAT</b> · Kodla. Tasarla. Gül. Devam Et. · Instagram: @ctrlzsizhayat</footer><script src="assets/app.js"></script></body></html>
<?php } ?>
