# CTRLZSIZ HAYAT

Bu sürümde Gmail OTP / mail kodu sistemi kaldırıldı. Kayıt ve giriş daha sade hale getirildi:

- Google ile Devam Et
- Normal ad soyad, e-posta ve şifre ile kayıt
- Admin panelinde sadece Google Ayarları kaldı
- Mail Ayarları ekranı kaldırıldı

## Kurulum

1. ZIP dosyasını çıkar.
2. Klasör adını `ctrlzsizhayat` yap.
3. XAMPP için `C:\xampp\htdocs\ctrlzsizhayat\` içine koy.
4. Apache'yi başlat.
5. Tarayıcıdan aç:

```text
http://localhost/ctrlzsizhayat/frontend/
```

## Admin girişi

Normal giriş ekranından şu bilgilerle admin paneline girilir:

```text
E-posta: admin@ctrlzsizhayat.com
Şifre: admin123
```

## Google ile giriş

Admin panelinden **Google Ayarları** sayfasına girip Google Cloud’dan aldığın bilgileri kaydet:

```text
GOOGLE_CLIENT_ID
GOOGLE_CLIENT_SECRET
```

Google Cloud Console’da Authorized redirect URI alanına şunu ekle:

```text
http://localhost/ctrlzsizhayat/frontend/google_callback.php
```

Google ayarları doğru girildiğinde kayıt ve giriş ekranındaki **Google ile Devam Et** butonu aktif olur.

## Normal kayıt

Google ile giriş kullanmadan da şu bilgilerle kayıt olunabilir:

```text
Ad Soyad
E-posta
Şifre
```

Kullanıcı kayıt olunca direkt sisteme giriş yapar. Şifreler veritabanında düz metin değil, güvenli hash olarak saklanır.

## Klasör yapısı

- `backend`
- `frontend`
- `uploads`
- `.env`
- `database.sql`
- `migration.sql`
- `migration_google_login.sql`
- `README.md`

## Not

Uygulama ilk çalıştığında `backend/database.sqlite` dosyasını otomatik oluşturur. Eski verilerden dolayı sorun yaşarsan `backend/database.sqlite` dosyasını silip sayfayı yenileyebilirsin.
