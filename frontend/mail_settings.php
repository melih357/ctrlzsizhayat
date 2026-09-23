<?php
require_once 'layout.php';
require_admin();

$env=read_env_assoc();
$hasPassword = !empty($env['SMTP_PASSWORD']) && !str_contains($env['SMTP_PASSWORD'],'BURAYA') && !str_contains($env['SMTP_PASSWORD'],'GOOGLE_UYGULAMA');

if($_SERVER['REQUEST_METHOD']==='POST'){
    $mailFrom=trim($_POST['mail_from_email'] ?? 'gelecegenot060@gmail.com');
    $mailName=trim($_POST['mail_from_name'] ?? 'CTRLZSIZ HAYAT');
    $host=trim($_POST['smtp_host'] ?? 'smtp.gmail.com');
    $port=trim($_POST['smtp_port'] ?? '465');
    $secure=trim($_POST['smtp_secure'] ?? 'ssl');
    $username=trim($_POST['smtp_username'] ?? $mailFrom);
    $password=preg_replace('/\s+/', '', $_POST['smtp_password'] ?? '');

    try{
        if(!filter_var($mailFrom,FILTER_VALIDATE_EMAIL)) throw new Exception('Gönderen e-posta geçersiz.');
        if(!filter_var($username,FILTER_VALIDATE_EMAIL)) throw new Exception('SMTP kullanıcı adı Gmail adresi olmalı.');
        if(!in_array($secure,['ssl','tls'],true)) throw new Exception('SMTP güvenlik değeri ssl veya tls olmalı.');
        if(!ctype_digit($port)) throw new Exception('SMTP port sayı olmalı.');

        $updates=[
            'MAIL_FROM_EMAIL'=>$mailFrom,
            'MAIL_FROM_NAME'=>$mailName,
            'SMTP_HOST'=>$host,
            'SMTP_PORT'=>$port,
            'SMTP_SECURE'=>$secure,
            'SMTP_USERNAME'=>$username,
        ];
        if($password!==''){
            if(strlen($password)<16) throw new Exception('Gmail Uygulama Şifresi genelde 16 karakterdir. Google’ın verdiği uygulama şifresini yaz.');
            $updates['SMTP_PASSWORD']=$password;
        } elseif(!$hasPassword){
            throw new Exception('Gmail Uygulama Şifresi boş bırakılamaz. Google’dan 16 haneli uygulama şifresi alıp buraya yazmalısın.');
        }

        save_env_values($updates);
        flash('Mail ayarları kaydedildi. Şimdi bu sayfadaki Test Mail Gönder butonuyla dene.');
        redirect(BASE_URL.'/mail_settings.php');
    }catch(Throwable $e){
        flash('Mail ayarları kaydedilemedi: '.$e->getMessage());
    }
}

if(isset($_GET['test'])){
    $to=$env['MAIL_FROM_EMAIL'] ?? 'gelecegenot060@gmail.com';
    $subject='CTRLZSIZ HAYAT test maili';
    $body="Bu test maili geldiyse Gmail SMTP ayarların doğru çalışıyor.\n\nArtık kayıt ekranındaki doğrulama kodları Gmail'e gelecektir.";
    if(send_app_mail($to,$subject,$body)){
        flash('Test maili gönderildi. Gmail gelen kutusu ve spam klasörünü kontrol et: '.$to);
    }else{
        flash('Test maili gönderilemedi. Hata: '.mail_last_error());
    }
    redirect(BASE_URL.'/mail_settings.php');
}

$env=read_env_assoc();
$hasPassword = !empty($env['SMTP_PASSWORD']) && !str_contains($env['SMTP_PASSWORD'],'BURAYA') && !str_contains($env['SMTP_PASSWORD'],'GOOGLE_UYGULAMA');
page_header('Gmail Mail Ayarları');
?>
<div class="hero">
  <div>
    <span class="pill">Admin Mail Ayarı</span>
    <h1>Gmail OTP gönderimini aktif et</h1>
    <p class="muted">Kodların gerçekten Gmail'e gitmesi için buraya Google Uygulama Şifresi girilmelidir. Normal Gmail şifresi çalışmaz.</p>
    <div class="card">
      <h3>Durum</h3>
      <p><b>SMTP Host:</b> <?=h($env['SMTP_HOST'] ?? 'boş')?></p>
      <p><b>SMTP Kullanıcı:</b> <?=h($env['SMTP_USERNAME'] ?? 'boş')?></p>
      <p><b>Uygulama Şifresi:</b> <?=$hasPassword ? '✅ Girilmiş' : '❌ Girilmemiş'?></p>
      <p><b>.env yazılabilir mi?</b> <?=is_writable(dirname(env_file_path())) ? '✅ Evet' : '❌ Hayır, klasör izni gerekli'?></p>
      <?php if($hasPassword): ?>
        <a class="btn primary" href="mail_settings.php?test=1">📩 Test Mail Gönder</a>
      <?php endif; ?>
    </div>
  </div>
  <form class="card form" method="post" style="max-width:560px">
    <h2>SMTP Ayarları</h2>
    <label>Gönderen E-posta</label>
    <input type="email" name="mail_from_email" value="<?=h($env['MAIL_FROM_EMAIL'] ?? 'gelecegenot060@gmail.com')?>" required>
    <label>Gönderen Adı</label>
    <input name="mail_from_name" value="<?=h($env['MAIL_FROM_NAME'] ?? 'CTRLZSIZ HAYAT')?>" required>
    <label>SMTP Host</label>
    <input name="smtp_host" value="<?=h($env['SMTP_HOST'] ?? 'smtp.gmail.com')?>" required>
    <label>SMTP Port</label>
    <input name="smtp_port" value="<?=h($env['SMTP_PORT'] ?? '465')?>" required>
    <label>SMTP Güvenlik</label>
    <select name="smtp_secure">
      <?php $sec=$env['SMTP_SECURE'] ?? 'ssl'; ?>
      <option value="ssl" <?=$sec==='ssl'?'selected':''?>>ssl - port 465</option>
      <option value="tls" <?=$sec==='tls'?'selected':''?>>tls - port 587</option>
    </select>
    <label>SMTP Kullanıcı Adı</label>
    <input type="email" name="smtp_username" value="<?=h($env['SMTP_USERNAME'] ?? 'gelecegenot060@gmail.com')?>" required>
    <label>Gmail Uygulama Şifresi</label>
    <input type="password" name="smtp_password" placeholder="<?=$hasPassword ? 'Değiştirmeyeceksen boş bırak' : '16 haneli Google uygulama şifresi'?>" <?=$hasPassword ? '' : 'required'?>>
    <p class="muted">Google şifreyi boşluklu gösterirse sorun değil; sistem boşlukları otomatik temizler. Normal Gmail şifresi çalışmaz.</p>
    <button class="btn primary" style="width:100%">Ayarları Kaydet</button>
  </form>
</div>
<div class="card">
  <h3>Nasıl alacaksın?</h3>
  <p>Google Hesabı → Güvenlik → 2 Adımlı Doğrulama → Uygulama Şifreleri bölümünden yeni uygulama şifresi oluştur. Oluşan 16 haneli şifreyi bu ekrana yaz.</p>
  <p><b>Admin giriş bilgisi:</b> admin@ctrlzsizhayat.com / admin123</p>
</div>
<?php page_footer(); ?>
