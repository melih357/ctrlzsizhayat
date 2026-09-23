# Google ile Giriş Kurulumu

Bu ZIP içinde normal e-posta/şifre kaydı hemen çalışır. Google ile giriş ise Google tarafından verilen proje bilgileriyle aktif edilir.

## Neden bende hazır gelmiyor?
Google Client ID ve Client Secret her site/domaine özel üretilir. Bu değerleri ZIP içine hazır koymak mümkün değildir; kendi Google hesabınla Google Cloud Console üzerinden alman gerekir.

## Projede kolay ayar ekranı var
1. Admin hesabıyla giriş yap:
   - E-posta: `admin@ctrlzsizhayat.com`
   - Şifre: `admin123`
2. Menüden **Google Ayarları** sayfasına gir.
3. Sayfadaki Redirect URI değerini kopyala.
4. Google Cloud Console → APIs & Services → Credentials bölümünden OAuth Client oluştur.
5. Authorized redirect URI alanına projedeki değeri ekle.
6. Client ID ve Client Secret değerlerini projedeki **Google Ayarları** sayfasına kaydet.

## Kayıt ekranı
Google bilgileri girilmemişse Google butonu pasif görünür, hata sayfasına düşmez. Normal kayıt çalışmaya devam eder.

Google bilgileri doğru girildiğinde buton aktif olur ve kullanıcı Google hesabıyla giriş yapabilir.
