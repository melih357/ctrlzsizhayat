<?php
require_once 'layout.php';
if(is_logged_in()) redirect(is_admin()?BASE_URL.'/admin.php':BASE_URL.'/index.php');

if(!google_auth_ready()){
    flash('Google ile giriş şu an aktif değil. Lütfen normal e-posta/şifre kaydını kullan.');
    redirect(BASE_URL.'/register.php');
}

$state=bin2hex(random_bytes(16));
$_SESSION['google_oauth_state']=$state;

$params=[
    'client_id'=>GOOGLE_CLIENT_ID,
    'redirect_uri'=>google_redirect_uri(),
    'response_type'=>'code',
    'scope'=>'openid email profile',
    'state'=>$state,
    'access_type'=>'online',
    'prompt'=>'select_account'
];

redirect('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($params));
?>
