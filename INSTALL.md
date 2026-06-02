# Panduan Instalasi SMCD

Satu Mimpi Central Dispatch — PHP Native + MySQL + Bootstrap 5

## Persyaratan

- PHP 8.0 atau lebih baru (disarankan 8.1+)
- Ekstensi PHP: `pdo`, `pdo_mysql`, `json`, `session`
- MySQL 5.7+ / MariaDB 10.3+
- Apache dengan `mod_rewrite` (opsional) atau Nginx
- Shared hosting **atau** VPS Ubuntu

---

## 1. Upload File

Upload seluruh isi repository ke document root web server:

- **Shared hosting:** `public_html/` atau folder domain Anda
- **VPS Ubuntu (Apache):** `/var/www/html/smcd/` atau virtual host Anda
- **VPS Ubuntu (Nginx):** root path pada blok `server`

Pastikan struktur folder tetap utuh (`api`, `assets`, `config`, `includes`, dll.).

---

## 2. Import Database

### Via phpMyAdmin
1. Buat database baru (misalnya `smcd_dispatch`)
2. Import file `database.sql`

### Via command line
```bash
mysql -u root -p < database.sql
```

File SQL akan membuat database `smcd_dispatch`, semua tabel, dan akun default.

---

## 3. Edit Config Database

```bash
cp config/database.php.example config/database.php
```

Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'smcd_dispatch');
define('DB_USER', 'username_mysql_anda');
define('DB_PASS', 'password_mysql_anda');
```

Pada shared hosting, gunakan kredensial dari panel hosting (cPanel, dll.).

---

## 4. Login Pertama

Buka browser:

```
https://domain-anda.com/login.php
```

| Role | Username | Password |
|------|----------|----------|
| Developer | `developer` | `developer123` |
| Dispatch | `dispatch` | `dispatch123` |

**Penting:** Ganti password default setelah login pertama (via phpMyAdmin atau script PHP).

### Membuat akun LSPD / BCSO

Jalankan di MySQL (ganti password hash dengan hasil `password_hash` PHP):

```sql
USE smcd_dispatch;

-- Contoh: officer LSPD (password: lspd123)
INSERT INTO users (username, password, role, display_name) VALUES
('lspd1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lspd', 'Officer Smith');

-- Contoh: deputy BCSO (password: bcso123)  
INSERT INTO users (username, password, role, display_name) VALUES
('bcso1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bcso', 'Deputy Jones');
```

> Hash di atas adalah contoh. Generate hash baru:
> `php -r "echo password_hash('password_anda', PASSWORD_DEFAULT);"`

---

## 5. Menjalankan Website

### Apache (VPS Ubuntu)

```bash
sudo apt update
sudo apt install apache2 php libapache2-mod-php php-mysql mysql-server
sudo systemctl enable apache2 mysql
```

Letakkan file di `/var/www/html/` lalu set permission:

```bash
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

### PHP Built-in Server (pengujian lokal)

```bash
cd /path/to/smcd
php -S localhost:8080
```

Buka `http://localhost:8080/login.php`

### Nginx (ringkas)

```nginx
server {
    listen 80;
    server_name smcd.local;
    root /var/www/smcd;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ ^/(config|includes)/ {
        deny all;
    }
}
```

---

## Alur Penggunaan

### Dispatcher / Developer
1. Login → Dashboard Dispatch
2. Buat panggilan 911, pursuit, atau BOLO
3. Assign unit ke call
4. Pantau Unit Status Board & Panic Alerts (realtime)

### Officer (LSPD / BCSO)
1. Login → isi **Unit Setup** (nama, callsign, pangkat)
2. Buka **Unit Panel** → update status 10-code
3. Gunakan **Panic Button** jika darurat

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Koneksi database gagal | Periksa `config/database.php` dan pastikan database diimport |
| Halaman kosong / 500 | Aktifkan `display_errors` di PHP untuk debug; cek log Apache/Nginx |
| API 403 CSRF | Pastikan session aktif; refresh halaman |
| Alarm tidak bunyi | Browser memblokir autoplay — klik halaman sekali lalu trigger panic |
| CSS/JS tidak load | Pastikan path `/assets/` dapat diakses; cek permission folder |

---

## Keamanan Produksi

1. Ganti semua password default
2. Gunakan HTTPS (SSL)
3. Jangan commit `config/database.php` ke repo publik
4. Batasi akses phpMyAdmin
5. Backup database secara berkala

---

**SMCD** — Satu Mimpi Central Dispatch | Emergency Communications Center
