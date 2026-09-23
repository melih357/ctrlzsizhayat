<?php require_once 'layout.php'; if(is_logged_in()) redirect(is_admin()?BASE_URL.'/admin.php':BASE_URL.'/index.php');

if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['name']??'');
    $email=strtolower(trim($_POST['email']??''));
    $pass=$_POST['password']??'';

    if(!$name || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($pass)<6){
        flash(t('Ad soyad, geçerli e-posta ve en az 6 haneli şifre gerekli.','Full name, valid email and a password of at least 6 characters are required.'));
    } else {
        try{
            $existing=db()->prepare('SELECT * FROM users WHERE email=?');
            $existing->execute([$email]);
            $u=$existing->fetch();

            if($u){
                flash(t('Bu e-posta zaten kayıtlı. Giriş yapabilirsin.','This email is already registered. You can log in.'));
                redirect(BASE_URL.'/login.php');
            }

            $s=db()->prepare('INSERT INTO users(name,email,password_hash,email_verified) VALUES(?,?,?,1)');
            $s->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);
            $_SESSION['user_id']=db()->lastInsertId();
            flash(t('Kayıt başarılı. Hoş geldin!','Registration successful. Welcome!'));
            redirect(BASE_URL.'/index.php');
        }catch(Exception $e){
            flash(t('Kayıt sırasında bir sorun oluştu. Lütfen tekrar dene.','An error occurred during registration. Please try again.').' Hata: '.$e->getMessage());
        }
    }
}

page_header(t('Kayıt Ol','Register')); ?>
<div class="auth-hero hero">
  <div>
    <span class="pill"><?=t('Hızlı ve kullanıcı dostu kayıt ✨','Fast and user-friendly registration ✨')?></span>
    <h1><span class="gradient">CTRLZSIZ</span> <?=t('dünyasına katıl','welcomes you')?></h1>
    <p class="muted"><?=t('İstersen Google hesabınla tek tıkla devam et, istersen ad soyad, e-posta ve şifreyle klasik kayıt ol. Gmail SMTP kodu bekleme sistemi kaldırıldı.','Continue with Google in one click or register with full name, email and password. The Gmail SMTP code waiting flow has been removed.')?></p>
    <div class="trust-row">
      <span>⚡ <?=t('Hızlı kayıt','Fast signup')?></span>
      <span>🔐 <?=t('Güvenli şifreleme','Secure password hashing')?></span>
      <span>👤 <?=t('Kolay giriş','Easy login')?></span>
    </div>
  </div>

  <form class="card form auth-card" method="post">
    <h2><?=t('Kayıt Ol','Register')?></h2>

    <?php if(google_auth_ready()): ?>
      <a class="btn google-btn" href="google_login.php" style="width:100%">
        <span class="google-g">G</span> <?=t('Google ile Devam Et','Continue with Google')?>
      </a>
    <?php else: ?>
      <button class="btn google-btn disabled" type="button" style="width:100%" disabled>
        <span class="google-g">G</span> <?=t('Google ile Devam Et','Continue with Google')?>
      </button>
      <p class="auth-note"><?=t('Google girişi admin panelinden aktif edilebilir. Normal kayıt hemen çalışır.','Google login can be enabled from the admin panel. Normal signup works now.')?></p>
    <?php endif; ?>

    <div class="divider"><span><?=t('veya','or')?></span></div>

    <input name="name" placeholder="<?=t('Ad Soyad','Full Name')?>" required>
    <input type="email" name="email" placeholder="<?=t('E-posta','Email')?>" required>
    <div class="password-wrap">
      <input id="regpass" type="password" name="password" placeholder="<?=t('Şifre','Password')?>" minlength="6" required>
      <button type="button" onclick="togglePassword('regpass')">👁</button>
    </div>
    <button class="btn primary" style="width:100%">✅ <?=t('Kayıt Ol','Register')?></button>
    <p><?=t('Zaten hesabın var mı?','Already have an account?')?> <a class="pill" href="login.php"><?=t('Giriş yap','Login')?></a></p>
  </form>
</div>
<?php page_footer(); ?>
