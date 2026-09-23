<?php require_once 'layout.php'; if(is_logged_in()) redirect(is_admin()?BASE_URL.'/admin.php':BASE_URL.'/index.php');
$email=strtolower(trim($_SESSION['verify_email'] ?? ($_GET['email'] ?? '')));
if(!$email || !filter_var($email,FILTER_VALIDATE_EMAIL)){
    flash(t('Önce kayıt formunu doldurmalısın.','You need to complete the registration form first.'));
    redirect(BASE_URL.'/register.php');
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action'] ?? 'verify';
    if($action==='resend'){
        $s=db()->prepare('SELECT id,email_verified FROM users WHERE email=?');
        $s->execute([$email]);
        $u=$s->fetch();
        if(!$u){ flash(t('Kullanıcı bulunamadı.','User was not found.')); redirect(BASE_URL.'/register.php'); }
        if((int)$u['email_verified']===1){ flash(t('Bu e-posta zaten doğrulanmış. Giriş yapabilirsin.','This email is already verified. You can log in.')); redirect(BASE_URL.'/login.php'); }
        $result=create_email_verification_code($u['id'],$email);
        if(!$result['ok'] && isset($result['wait'])) flash(t('Yeni kod için '.$result['wait'].' saniye bekle.','Wait '.$result['wait'].' seconds for a new code.'));
        else {
            if(!empty($result['mail_sent'])) flash(t('Yeni doğrulama kodu e-postana gönderildi.','A new verification code has been sent to your email.'));
            else flash(t('Kod üretildi ama Gmail gönderimi başarısız oldu. .env içindeki SMTP_PASSWORD alanını kontrol et. Hata: '.($result['mail_error'] ?? ''),'Code was created but Gmail sending failed. Check SMTP_PASSWORD in .env. Error: '.($result['mail_error'] ?? '')));
        }
        redirect(BASE_URL.'/verify_email.php');
    }

    $code=preg_replace('/\D/','',$_POST['code']??'');
    if(strlen($code)!==6){
        flash(t('Lütfen 6 haneli doğrulama kodunu gir.','Please enter the 6-digit verification code.'));
    } else {
        $result=verify_email_code($email,$code);
        if($result['ok']){
            unset($_SESSION['verify_email']);
            $_SESSION['user_id']=$result['user_id'];
            flash(t('E-posta doğrulandı. Hoş geldin!','Email verified. Welcome!'));
            redirect(BASE_URL.'/index.php');
        } else {
            flash($result['message']);
        }
    }
}
page_header(t('E-posta Doğrulama','Email Verification')); ?>
<div class="auth-hero hero"><div><span class="pill">📩 <?=t('Tek kullanımlık kod','One-time code')?></span><h1><?=t('E-postanı','Verify your')?> <span class="gradient"><?=t('doğrula','email')?></span></h1><p class="muted"><?=t('6 haneli kod şu e-postaya gönderildi:','The 6-digit code was sent to this email:')?> <b><?=h($email)?></b></p><p class="muted"><?=t('Kod 10 dakika geçerlidir ve sadece bir kez kullanılabilir.','The code is valid for 10 minutes and can only be used once.')?></p></div><form class="card form auth-card" method="post"><h2><?=t('Doğrulama Kodu','Verification Code')?></h2><input name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="123456" required><button class="btn primary" style="width:100%">✅ <?=t('Hesabı Aktifleştir','Activate Account')?></button></form></div>
<form method="post" style="max-width:520px;margin:18px auto 0;text-align:center"><input type="hidden" name="action" value="resend"><button class="pill" type="submit">↻ <?=t('Kodu tekrar gönder','Resend code')?></button></form>
<?php page_footer(); ?>
