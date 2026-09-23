<?php
require_once 'layout.php';
require_admin();

$env=read_env_assoc();
$currentId=$env['GOOGLE_CLIENT_ID'] ?? '';
$currentSecret=$env['GOOGLE_CLIENT_SECRET'] ?? '';
$currentRedirect=$env['GOOGLE_REDIRECT_URI'] ?? '';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $clientId=trim($_POST['google_client_id'] ?? '');
    $secretInput=trim($_POST['google_client_secret'] ?? '');
    $redirectUri=trim($_POST['google_redirect_uri'] ?? '');

    $updates=[
        'GOOGLE_CLIENT_ID'=>$clientId,
        'GOOGLE_REDIRECT_URI'=>$redirectUri
    ];
    if($secretInput!==''){
        $updates['GOOGLE_CLIENT_SECRET']=$secretInput;
    } elseif($currentSecret===''){
        $updates['GOOGLE_CLIENT_SECRET']='';
    }

    try{
        save_env_values($updates);
        flash('Google ayarları kaydedildi. Client ID ve Secret doğruysa Google ile giriş aktif olur.');
        redirect(BASE_URL.'/google_settings.php');
    }catch(Throwable $e){
        flash('Ayarlar kaydedilemedi: '.$e->getMessage());
    }
}

$env=read_env_assoc();
$hasId=!empty($env['GOOGLE_CLIENT_ID']) && str_ends_with(trim($env['GOOGLE_CLIENT_ID']),'.apps.googleusercontent.com');
$hasSecret=!empty($env['GOOGLE_CLIENT_SECRET']) && strlen(trim($env['GOOGLE_CLIENT_SECRET']))>=20;
$ready=$hasId && $hasSecret;
$defaultRedirect=google_redirect_uri();

page_header('Google Ayarları');
?>
<div class="hero">
  <div>
    <span class="pill">Google OAuth</span>
    <h1><span class="gradient">Google ile Giriş</span> Ayarları</h1>
    <p class="muted">Buradan Google Client ID ve Client Secret değerlerini kaydedebilirsin. Bu değerler her site/proje için Google Cloud Console üzerinden alınır.</p>
  </div>
</div>

<div class="grid two">
  <section class="card form">
    <h2>Ayar Durumu</h2>
    <p><?= $hasId ? '✅ Client ID girilmiş' : '❌ Client ID eksik veya geçersiz' ?></p>
    <p><?= $hasSecret ? '✅ Client Secret girilmiş' : '❌ Client Secret eksik veya geçersiz' ?></p>
    <p><?= $ready ? '✅ Google ile giriş aktif görünüyor' : '⚠️ Google ile giriş henüz aktif değil' ?></p>
    <hr>
    <p class="muted">Google Cloud Console’da Authorized redirect URI alanına şunu ekle:</p>
    <input value="<?=h($defaultRedirect)?>" readonly onclick="this.select()">
  </section>

  <form class="card form" method="post">
    <h2>Google Bilgilerini Kaydet</h2>
    <label>GOOGLE_CLIENT_ID</label>
    <input name="google_client_id" value="<?=h($env['GOOGLE_CLIENT_ID'] ?? '')?>" placeholder="...apps.googleusercontent.com">

    <label>GOOGLE_CLIENT_SECRET</label>
    <input name="google_client_secret" value="" placeholder="Gizli değer. Değiştirmeyeceksen boş bırak.">
    <?php if(!empty($env['GOOGLE_CLIENT_SECRET'])): ?>
      <p class="auth-note">Client Secret kayıtlı. Güvenlik için burada gösterilmez.</p>
    <?php endif; ?>

    <label>GOOGLE_REDIRECT_URI <small>(boş bırakabilirsin)</small></label>
    <input name="google_redirect_uri" value="<?=h($env['GOOGLE_REDIRECT_URI'] ?? '')?>" placeholder="<?=h($defaultRedirect)?>">

    <button class="btn primary" style="width:100%">Kaydet</button>
    <a class="btn" style="width:100%;text-align:center" href="register.php">Kayıt ekranını kontrol et</a>
  </form>
</div>

<section class="card" style="margin-top:18px">
  <h2>Google Cloud’da yapılacak kısa işlem</h2>
  <ol class="muted">
    <li>Google Cloud Console → APIs & Services → Credentials bölümüne gir.</li>
    <li>Create Credentials → OAuth client ID seç.</li>
    <li>Application type: Web application seç.</li>
    <li>Authorized redirect URI alanına yukarıdaki bağlantıyı ekle.</li>
    <li>Client ID ve Client Secret değerlerini bu sayfaya kaydet.</li>
  </ol>
</section>
<?php page_footer(); ?>
