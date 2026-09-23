<?php
session_start();

function load_env_file(){
    static $loaded=false;
    if($loaded) return;
    $loaded=true;
    $envPath=dirname(__DIR__).'/.env';
    if(!is_file($envPath)) return;
    foreach(file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line){
        $line=trim($line);
        if($line==='' || str_starts_with($line,'#') || !str_contains($line,'=')) continue;
        [$key,$value]=explode('=',$line,2);
        $key=trim($key);
        $value=trim($value, " \"'");
        if($key!=='' && getenv($key)===false) putenv($key.'='.$value);
    }
}
function env_value($key,$default=null){ load_env_file(); $v=getenv($key); return $v===false ? $default : $v; }

define('APP_NAME', env_value('APP_NAME','CTRLZSIZ HAYAT'));
define('BASE_URL','/ctrlzsizhayat/frontend');
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_PATH . '/uploads');
define('UPLOAD_URL', '/ctrlzsizhayat/uploads');
define('GOOGLE_CLIENT_ID', env_value('GOOGLE_CLIENT_ID',''));
define('GOOGLE_CLIENT_SECRET', env_value('GOOGLE_CLIENT_SECRET',''));
define('GOOGLE_REDIRECT_URI', env_value('GOOGLE_REDIRECT_URI',''));
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);

function db(){
    static $pdo=null;
    if($pdo===null){
        $pdo = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        init_db($pdo);
    }
    return $pdo;
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect($url){ header('Location: '.$url); exit; }
function is_logged_in(){ return isset($_SESSION['user_id']); }
function current_user(){
    if(!is_logged_in()) return null;
    $s=db()->prepare('SELECT * FROM users WHERE id=?');
    $s->execute([$_SESSION['user_id']]);
    $u=$s->fetch();
    if(!$u){ unset($_SESSION['user_id']); return null; }
    return $u;
}
function is_admin(){ $u=current_user(); return $u && (int)$u['is_admin']===1; }
function require_auth(){ if(!current_user()) redirect(BASE_URL.'/register.php'); }
function require_admin(){ require_auth(); if(!is_admin()) redirect(BASE_URL.'/index.php'); }

function get_lang(){ if(isset($_GET['lang']) && in_array($_GET['lang'],['tr','en'])) $_SESSION['lang']=$_GET['lang']; return $_SESSION['lang'] ?? 'tr'; }
function t($tr,$en){ return get_lang()==='en' ? $en : $tr; }
function get_currency(){ if(isset($_GET['currency']) && in_array($_GET['currency'],['TRY','USD','EUR'])) $_SESSION['currency']=$_GET['currency']; return $_SESSION['currency'] ?? 'TRY'; }
function fx_rates(){
    static $rates=null; if($rates!==null) return $rates;
    $rates=['TRY'=>1,'USD'=>0.031,'EUR'=>0.029,'date'=>'offline'];
    try{
        $ctx=stream_context_create(['http'=>['timeout'=>2]]);
        $json=@file_get_contents('https://api.frankfurter.dev/v2/rates?base=TRY&quotes=USD,EUR', false, $ctx);
        if($json){ $data=json_decode($json,true); if(isset($data['rates']['USD'],$data['rates']['EUR'])){ $rates=['TRY'=>1,'USD'=>(float)$data['rates']['USD'],'EUR'=>(float)$data['rates']['EUR'],'date'=>$data['date']??date('Y-m-d')]; }}
    }catch(Throwable $e){}
    return $rates;
}
function convert_amount($try,$currency=null){ $currency=$currency?:get_currency(); $r=fx_rates(); return (float)$try * ($r[$currency] ?? 1); }
function money($v,$currency=null){ $currency=$currency?:get_currency(); $amount=convert_amount($v,$currency); $sym=['TRY'=>'₺','USD'=>'$','EUR'=>'€'][$currency]??'₺'; if($currency==='TRY') return number_format($amount,2,',','.') . ' TL'; return $sym . number_format($amount,2,'.',','); }
function money_try($v){ return number_format((float)$v, 2, ',', '.') . ' TL'; }
function add_col($pdo,$table,$col,$def){ $cols=$pdo->query("PRAGMA table_info($table)")->fetchAll(); foreach($cols as $c){ if($c['name']===$col) return; } $pdo->exec("ALTER TABLE $table ADD COLUMN $col $def"); }

function env_file_path(){ return dirname(__DIR__).'/.env'; }
function read_env_assoc(){
    $path=env_file_path();
    $data=[];
    if(is_file($path)){
        foreach(file($path, FILE_IGNORE_NEW_LINES) as $line){
            $trim=trim($line);
            if($trim==='' || str_starts_with($trim,'#') || !str_contains($line,'=')) continue;
            [$k,$v]=explode('=',$line,2);
            $data[trim($k)]=trim($v, " \"'");
        }
    }
    return $data;
}
function save_env_values($updates){
    $path=env_file_path();
    $lines=is_file($path) ? file($path, FILE_IGNORE_NEW_LINES) : [];
    $seen=[];
    foreach($lines as &$line){
        if(trim($line)==='' || str_starts_with(trim($line),'#') || !str_contains($line,'=')) continue;
        [$k,$v]=explode('=',$line,2);
        $key=trim($k);
        if(array_key_exists($key,$updates)){
            $value=(string)$updates[$key];
            $line=$key.'='.$value;
            $seen[$key]=true;
        }
    }
    foreach($updates as $key=>$value){
        if(!isset($seen[$key])) $lines[]=$key.'='.(string)$value;
        putenv($key.'='.(string)$value);
    }
    if(!is_writable(dirname($path))) throw new Exception('.env klasörüne yazma izni yok: '.dirname($path));
    file_put_contents($path, implode("\n", $lines)."\n");
}

function google_auth_ready(){
    $id=trim(GOOGLE_CLIENT_ID);
    $secret=trim(GOOGLE_CLIENT_SECRET);
    if($id==='' || $secret==='') return false;
    if(str_contains($id,'BURAYA') || str_contains($id,'PASTE') || str_contains($secret,'BURAYA') || str_contains($secret,'PASTE')) return false;
    // Google OAuth Web Client ID genelde .apps.googleusercontent.com ile biter.
    return str_ends_with($id,'.apps.googleusercontent.com') && strlen($secret) >= 20;
}
function google_redirect_uri(){
    if(GOOGLE_REDIRECT_URI) return GOOGLE_REDIRECT_URI;
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme.'://'.$host.BASE_URL.'/google_callback.php';
}
function google_config_error_message(){
    if(!GOOGLE_CLIENT_ID) return 'GOOGLE_CLIENT_ID boş. Admin panelinden Google Ayarları sayfasına girip Client ID değerini kaydet.';
    if(!GOOGLE_CLIENT_SECRET) return 'GOOGLE_CLIENT_SECRET boş. Admin panelinden Google Ayarları sayfasına girip Client Secret değerini kaydet.';
    if(!str_ends_with(trim(GOOGLE_CLIENT_ID),'.apps.googleusercontent.com')) return 'GOOGLE_CLIENT_ID geçerli görünmüyor. Google Cloud Console’dan Web application OAuth Client ID almalısın.';
    if(strlen(trim(GOOGLE_CLIENT_SECRET)) < 20) return 'GOOGLE_CLIENT_SECRET geçerli görünmüyor. Google Cloud Console’dan alınan Client Secret değerini yazmalısın.';
    return '';
}
function http_post_form($url,$fields){
    $context=stream_context_create(['http'=>[
        'method'=>'POST',
        'header'=>"Content-Type: application/x-www-form-urlencoded\r\n",
        'content'=>http_build_query($fields),
        'timeout'=>20,
        'ignore_errors'=>true
    ]]);
    $response=@file_get_contents($url,false,$context);
    if($response===false) throw new Exception('Google sunucusuna bağlanılamadı.');
    return json_decode($response,true) ?: [];
}
function http_get_json($url,$accessToken=null){
    $headers=[];
    if($accessToken) $headers[]='Authorization: Bearer '.$accessToken;
    $context=stream_context_create(['http'=>[
        'method'=>'GET',
        'header'=>implode("\r\n",$headers),
        'timeout'=>20,
        'ignore_errors'=>true
    ]]);
    $response=@file_get_contents($url,false,$context);
    if($response===false) throw new Exception('Google kullanıcı bilgisi alınamadı.');
    return json_decode($response,true) ?: [];
}
function login_or_create_google_user($googleUser){
    $email=strtolower(trim($googleUser['email'] ?? ''));
    $name=trim($googleUser['name'] ?? '');
    $googleId=trim($googleUser['sub'] ?? '');
    if(!$email || !$googleId) throw new Exception('Google hesabından e-posta bilgisi alınamadı.');
    if(($googleUser['email_verified'] ?? false)!==true) throw new Exception('Google e-posta adresi doğrulanmamış görünüyor.');
    if($name==='') $name=explode('@',$email)[0];

    $pdo=db();
    $s=$pdo->prepare('SELECT * FROM users WHERE email=? OR google_id=? LIMIT 1');
    $s->execute([$email,$googleId]);
    $user=$s->fetch();
    if($user){
        $pdo->prepare('UPDATE users SET google_id=?, email_verified=1 WHERE id=?')->execute([$googleId,$user['id']]);
        $_SESSION['user_id']=$user['id'];
        return $user['id'];
    }

    $randomPass=password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT);
    $ins=$pdo->prepare('INSERT INTO users(name,email,password_hash,is_admin,email_verified,google_id) VALUES(?,?,?,?,?,?)');
    $ins->execute([$name,$email,$randomPass,0,1,$googleId]);
    $id=db()->lastInsertId();
    $_SESSION['user_id']=$id;
    return $id;
}

function flash($msg=null){ if($msg!==null){ $_SESSION['flash']=$msg; return; } if(isset($_SESSION['flash'])){ $m=$_SESSION['flash']; unset($_SESSION['flash']); return '<div class="flash">'.h($m).'</div>'; } return ''; }

function smtp_read($fp){
    $data='';
    while(!feof($fp)){
        $line=fgets($fp, 515);
        if($line===false) break;
        $data.=$line;
        if(strlen($line)>=4 && $line[3]===' ') break;
    }
    return $data;
}
function smtp_cmd($fp,$cmd,$expected=[250]){
    if($cmd!==null) fwrite($fp,$cmd."\r\n");
    $resp=smtp_read($fp);
    $code=(int)substr($resp,0,3);
    if(!in_array($code,$expected,true)) throw new Exception('SMTP cevap hatası: '.$resp);
    return $resp;
}
function smtp_escape_message($message){
    $message=str_replace(["\r\n","\r"],"\n",$message);
    $lines=explode("\n",$message);
    foreach($lines as &$line){
        if(isset($line[0]) && $line[0]==='.') $line='.'.$line;
    }
    return implode("\r\n",$lines);
}

function smtp_config_error_message(){
    if(!SMTP_HOST) return 'SMTP_HOST boş. Mail Ayarları sayfasından smtp.gmail.com olarak kaydet.';
    if(!SMTP_USERNAME) return 'SMTP_USERNAME boş. Mail Ayarları sayfasından Gmail adresini yaz.';
    if(!SMTP_PASSWORD || SMTP_PASSWORD==='' || str_contains(SMTP_PASSWORD,'BURAYA') || str_contains(SMTP_PASSWORD,'GOOGLE_UYGULAMA')) return 'Gmail Uygulama Şifresi girilmemiş. Admin hesabıyla Mail Ayarları sayfasına girip 16 haneli Google Uygulama Şifresini yaz.';
    return '';
}

function init_db($pdo){
    $pdo->exec("CREATE TABLE IF NOT EXISTS users(id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,email TEXT UNIQUE NOT NULL,password_hash TEXT NOT NULL,is_admin INTEGER DEFAULT 0,email_verified INTEGER DEFAULT 0,google_id TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS email_verification_codes(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,email TEXT NOT NULL,code_hash TEXT NOT NULL,expires_at TEXT NOT NULL,attempts INTEGER DEFAULT 0,used INTEGER DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories(id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT UNIQUE NOT NULL,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS products(id INTEGER PRIMARY KEY AUTOINCREMENT,category_id INTEGER NOT NULL,name TEXT NOT NULL,description TEXT,price REAL NOT NULL DEFAULT 0,stock INTEGER NOT NULL DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(category_id) REFERENCES categories(id))");
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_images(id INTEGER PRIMARY KEY AUTOINCREMENT,product_id INTEGER NOT NULL,image_path TEXT NOT NULL,created_at TEXT DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS favorites(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,product_id INTEGER NOT NULL,created_at TEXT DEFAULT CURRENT_TIMESTAMP,UNIQUE(user_id,product_id))");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cart(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,product_id INTEGER NOT NULL,quantity INTEGER NOT NULL DEFAULT 1,UNIQUE(user_id,product_id))");
    $pdo->exec("CREATE TABLE IF NOT EXISTS addresses(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,title TEXT,fullname TEXT,phone TEXT,province TEXT,district TEXT,neighborhood TEXT,address TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cards(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,card_name TEXT,card_last4 TEXT,expiry TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,total REAL NOT NULL,status TEXT DEFAULT 'Hazırlanıyor',fullname TEXT,phone TEXT,province TEXT,district TEXT,neighborhood TEXT,address TEXT,card_last4 TEXT,currency TEXT DEFAULT 'TRY',converted_total REAL DEFAULT 0,rate_date TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items(id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER NOT NULL,product_id INTEGER NOT NULL,product_name TEXT,price REAL,quantity INTEGER,FOREIGN KEY(order_id) REFERENCES orders(id))");
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,product_id INTEGER NOT NULL,rating INTEGER NOT NULL,comment TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP,UNIQUE(user_id,product_id))");
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,name TEXT,email TEXT,message TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    add_col($pdo,'users','email_verified','INTEGER DEFAULT 0');
    add_col($pdo,'users','google_id','TEXT');
    add_col($pdo,'addresses','neighborhood','TEXT');
    add_col($pdo,'orders','neighborhood','TEXT');
    add_col($pdo,'orders','currency',"TEXT DEFAULT 'TRY'");
    add_col($pdo,'orders','converted_total','REAL DEFAULT 0');
    add_col($pdo,'orders','rate_date','TEXT');
    $pdo->exec('UPDATE users SET email_verified=1 WHERE is_admin=1');

    $count=(int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if($count===0){
        $adminPass=password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users(name,email,password_hash,is_admin,email_verified) VALUES(?,?,?,?,1)')->execute(['Admin','admin@ctrlzsizhayat.com',$adminPass,1]);
        $cats=['Anahtarlık','Tişört','Şort','Sticker','Cüzdan ve Kartlık','Şapka','Hırka'];
        foreach($cats as $cat){ $pdo->prepare('INSERT OR IGNORE INTO categories(name) VALUES(?)')->execute([$cat]); }
        $products=[
            ['Tişört','CTRLZ Oversize Tişört','Yumuşak kumaşlı, rahat kesimli günlük tişört.',399,25],
            ['Hırka','Renkli Kod Hırka','Canlı renk dokunuşlu sıcak ve rahat hırka.',799,14],
            ['Şapka','CTRLZ Logo Şapka','Günlük kullanım için ayarlanabilir logo şapka.',249,30],
            ['Sticker','Kodla Tasarla Sticker Seti','Laptop, defter ve telefon için eğlenceli sticker seti.',89,80],
            ['Anahtarlık','CTRLZ Anahtarlık','Dayanıklı metal anahtarlık.',129,50],
            ['Cüzdan ve Kartlık','Minimal Kartlık','Şık, kompakt ve günlük kullanıma uygun kartlık.',299,20],
            ['Şort','Rahat Kesim Şort','Yaz ayları için hafif kumaşlı rahat şort.',349,22]
        ];
        foreach($products as $p){
            $cid=$pdo->prepare('SELECT id FROM categories WHERE name=?'); $cid->execute([$p[0]]); $catId=$cid->fetchColumn();
            if($catId){
                $pdo->prepare('INSERT INTO products(category_id,name,description,price,stock) VALUES(?,?,?,?,?)')->execute([$catId,$p[1],$p[2],$p[3],$p[4]]);
                $pid=$pdo->lastInsertId();
                $pdo->prepare('INSERT INTO product_images(product_id,image_path) VALUES(?,?)')->execute([$pid,'logo.jpg']);
            }
        }
    }
}

db();
?>
