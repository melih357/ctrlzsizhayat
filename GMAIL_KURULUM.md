# Gmail OTP Kurulumu

Bu projede e-posta doğrulama kodlarının gerçek Gmail hesabına gönderilmesi için Gmail SMTP kullanılır.

## Neden ZIP direkt çalışmıyor?

Gmail, dış uygulamalardan mail göndermek için normal hesap şifresini kabul etmez. Ayrıca güvenlik nedeniyle senin gerçek Gmail uygulama şifren ZIP dosyasına koyulamaz.

## Tek seferlik kurulum

1. Google hesabında 2 Adımlı Doğrulama aç.
2. Google Hesabı > Güvenlik > Uygulama Şifreleri bölümünden 16 haneli uygulama şifresi oluştur.
3. Projede admin hesabıyla giriş yap:
   - E-posta: admin@ctrlzsizhayat.com
   - Şifre: admin123
4. Üst menüden Mail Ayarları sayfasına gir.
5. 16 haneli Gmail Uygulama Şifresini yaz ve kaydet.
6. Yeni kullanıcı kaydı açarak test et.

## Varsayılan SMTP ayarı

MAIL_FROM_EMAIL=gelecegenot060@gmail.com
SMTP_HOST=smtp.gmail.com
SMTP_PORT=465
SMTP_SECURE=ssl
SMTP_USERNAME=gelecegenot060@gmail.com

SMTP_PASSWORD alanı Mail Ayarları ekranından doldurulmalıdır.

## Hata alırsan

- 2 Adımlı Doğrulama açık mı?
- Uygulama Şifresi doğru mu?
- Normal Gmail şifresi yazmadığından emin misin?
- XAMPP/PHP OpenSSL aktif mi?
- Spam/Gereksiz klasörünü kontrol ettin mi?

Hata detayı ayrıca backend/mail_debug.log içine yazılır.
