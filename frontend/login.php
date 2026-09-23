<?php require_once 'layout.php'; if(is_logged_in()) redirect(is_admin()?BASE_URL.'/admin.php':BASE_URL.'/index.php');

if($_SERVER['REQUEST_METHOD']==='POST'){
    $email=strtolower(trim($_POST['email']??''));
    $pass=$_POST['password']??'';
    $s=db()->prepare('SELECT * FROM users WHERE email=?');
    $s->execute([$email]);
    $u=$s->fetch();

    if($u && password_verify($pass,$u['password_hash'])){
        $_SESSION['user_id']=$u['id'];
        redirect((int)$u['is_admin']===1?BASE_URL.'/admin.php':BASE_URL.'/index.php');
    } else {
        flash(t('E-posta veya şifre hatalı.','Email or password is incorrect.'));
    }
}

page_header(t('Giriş Yap','Login')); ?>
<div class="auth-hero hero">
  <div>
    <span class="pill"><?=t('Kolay giriş deneyimi 👋','Easy login experience 👋')?></span>
    <h1><?=t('Bugün tarzına','Add a premium touch to your')?> <span class="gradient"><?=t('premium dokunuş kat','style today')?></span></h1>
    <p class="muted"><?=t('Google ile tek tıkla giriş yapabilir veya e-posta ve şifrenle devam edebilirsin.','Sign in with Google in one click or continue with your email and password.')?></p>
  </div>

  <form class="card form auth-card" method="post">
    <h2><?=t('Giriş Yap','Login')?></h2>

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

    <input type="email" name="email" placeholder="<?=t('E-posta','Email')?>" required>
    <div class="password-wrap">
      <input id="loginpass" type="password" name="password" placeholder="<?=t('Şifre','Password')?>" required>
      <button type="button" onclick="togglePassword('loginpass')">👁</button>
    </div>
    <button class="btn primary" style="width:100%">👑 <?=t('Giriş Yap','Login')?></button>
    <p><?=t('Hesabın yok mu?','No account?')?> <a class="pill" href="register.php"><?=t('Kayıt ol','Register')?></a></p>
  </form>
</div>
<?php page_footer(); ?>
