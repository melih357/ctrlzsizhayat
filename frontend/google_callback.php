<?php
require_once 'layout.php';
if(is_logged_in()) redirect(is_admin()?BASE_URL.'/admin.php':BASE_URL.'/index.php');

try{
    if(!google_auth_ready()) throw new Exception('Google giriş ayarları eksik.');
    if(isset($_GET['error'])) throw new Exception('Google giriş iptal edildi veya hata verdi: '.$_GET['error']);

    $state=$_GET['state'] ?? '';
    if(!$state || !isset($_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'],$state)){
        throw new Exception('Google güvenlik doğrulaması başarısız oldu. Lütfen tekrar dene.');
    }
    unset($_SESSION['google_oauth_state']);

    $code=$_GET['code'] ?? '';
    if(!$code) throw new Exception('Google doğrulama kodu alınamadı.');

    $token=http_post_form('https://oauth2.googleapis.com/token',[
        'code'=>$code,
        'client_id'=>GOOGLE_CLIENT_ID,
        'client_secret'=>GOOGLE_CLIENT_SECRET,
        'redirect_uri'=>google_redirect_uri(),
        'grant_type'=>'authorization_code'
    ]);

    if(empty($token['access_token'])){
        $msg=$token['error_description'] ?? ($token['error'] ?? 'Token alınamadı.');
        throw new Exception('Google token hatası: '.$msg);
    }

    $googleUser=http_get_json('https://openidconnect.googleapis.com/v1/userinfo',$token['access_token']);
    login_or_create_google_user($googleUser);
    flash('Google ile giriş başarılı. Hoş geldin!');
    redirect(BASE_URL.'/index.php');
}catch(Throwable $e){
    flash('Google ile giriş başarısız: '.$e->getMessage());
    redirect(BASE_URL.'/login.php');
}
?>
