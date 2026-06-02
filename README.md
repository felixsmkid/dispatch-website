# Satu Mimpi Central Dispatch (SMCD)

**Emergency Communications Center** — Sistem CAD/Dispatch FiveM Roleplay berbasis **PHP Native**, **MySQL**, dan **Bootstrap 5**.

Tidak memerlukan Node.js, Docker, atau dependency kompleks. Cocok untuk shared hosting maupun VPS Ubuntu.

## Fitur Utama

- Dashboard Dispatch realtime (polling 3 detik)
- 911 Call Center (create, edit, assign, close)
- Unit Status Board (10-codes)
- Pursuit System (PIT, Spike, Air Unit)
- BOLO System
- Panic Button dengan overlay merah & alarm
- Active Units (LSPD & BCSO branding)
- Halaman Radio Codes lengkap
- Dark mode default, responsive mobile & desktop

## Role

| Role | Akses |
|------|--------|
| **Developer** | Full access |
| **Dispatch** | Mengelola seluruh CAD |
| **LSPD** | Officer — unit panel & panic |
| **BCSO** | Deputy — unit panel & panic |

## Akun Default

| Username | Password | Role |
|----------|----------|------|
| `developer` | `developer123` | Developer |
| `dispatch` | `dispatch123` | Dispatch |
| `lspd1` | `lspd123` | LSPD Officer (demo) |
| `bcso1` | `bcso123` | BCSO Deputy (demo) |

## Instalasi Cepat

Lihat **[INSTALL.md](INSTALL.md)** untuk panduan lengkap.

1. Upload semua file ke web root
2. Import `database.sql`
3. Salin & edit `config/database.php`
4. Login di `/login.php`

## Struktur Folder

```
├── api/              # REST API (JSON)
├── assets/
│   ├── css/
│   ├── js/
│   ├── img/          # Logo SMCD, LSPD, BCSO, favicon
│   └── sounds/       # Alarm panic
├── config/
├── includes/
├── dashboard.php
├── officer.php
├── unit-setup.php
├── radio-codes.php
├── login.php
└── database.sql
```

## Keamanan

- PDO Prepared Statements
- Password hashing (`password_hash`)
- Session protection (httponly, strict mode)
- CSRF token pada semua POST API
- Input validation & XSS escaping (`htmlspecialchars`)

## Lisensi

Proyek ini disediakan untuk penggunaan server roleplay FiveM Anda.

## Preview UI (GitHub Pages)

Tanpa server PHP — lihat UI dulu dengan data dummy:

1. Repo → **Settings** → **Pages**
2. Source: **Deploy from a branch** → branch `main` (atau branch PR) → folder **`/docs`**
3. Save → buka: `https://felixsmkid.github.io/dispatch-website/`

Atau buka file `docs/index.html` langsung di browser (beberapa fitur audio mungkin diblokir).

> Versi penuh (login, database, realtime) tetap butuh hosting PHP + MySQL.
