-- Mevcut veritabanı için güvenli geçiş notları.
-- Uygulama backend/config.php içinde eksik kolonları otomatik ekler.
-- Manuel kullanmak isterseniz:
ALTER TABLE addresses ADD COLUMN neighborhood TEXT;
ALTER TABLE orders ADD COLUMN neighborhood TEXT;
ALTER TABLE orders ADD COLUMN currency TEXT DEFAULT 'TRY';
ALTER TABLE orders ADD COLUMN converted_total REAL DEFAULT 0;
ALTER TABLE orders ADD COLUMN rate_date TEXT;

-- E-posta OTP doğrulama sistemi
ALTER TABLE users ADD COLUMN email_verified INTEGER DEFAULT 0;
CREATE TABLE IF NOT EXISTS email_verification_codes(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,email TEXT NOT NULL,code_hash TEXT NOT NULL,expires_at TEXT NOT NULL,attempts INTEGER DEFAULT 0,used INTEGER DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
UPDATE users SET email_verified=1 WHERE is_admin=1;
