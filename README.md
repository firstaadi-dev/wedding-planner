<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Deploy ke Render

Project ini sudah disiapkan dengan blueprint `render.yaml`.

### 1) Push repo ke GitHub
Pastikan file berikut ikut ter-push:
- `render.yaml`
- `scripts/render-start.sh`

### 2) Buat Web Service di Render (Blueprint)
- Di Render, pilih **New +** -> **Blueprint**
- Pilih repo ini
- Render akan membaca `render.yaml` otomatis

### 3) Isi Environment Variables (yang `sync: false`)
Wajib diisi:
- `APP_KEY` (format Laravel, contoh: `base64:...`)
- `APP_URL` (URL service Render Anda)
- `DB_HOST` (Neon direct host, disarankan bukan `-pooler` untuk migrate)
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

Sudah diset default di blueprint:
- `DB_CONNECTION=pgsql`
- `DB_PORT=5432`
- `DB_SSLMODE=require`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `RUN_MIGRATIONS=true`

### 4) Deploy
- Runtime: `docker` (build dari `Dockerfile`)
- Saat container start, script `scripts/render-entrypoint.sh` akan:
  - clear cache Laravel
  - jalankan `php artisan migrate --force` (jika `RUN_MIGRATIONS=true`)
  - bind Apache ke env `PORT` dari Render
  - start Apache

Jika startup sering timeout saat migrate, bisa atur:
- `RUN_MIGRATIONS=false` (jalankan migrate manual/terpisah)
- `MIGRATION_TIMEOUT_SECONDS=60` (default 60 detik)

### 5) Catatan Neon
- Untuk proses migration/seeding, endpoint Neon **direct** lebih aman daripada `-pooler`.
- Disarankan tetap pakai host direct di Render agar migrasi stabil.

## WorkOS Auth Migration

Project ini sekarang menggunakan WorkOS untuk login/register (hosted AuthKit).

1. Install dependency:
   ```bash
   composer require workos/workos-php
   ```
2. Isi konfigurasi di `.env`:
   ```bash
   WORKOS_API_KEY=sk_test_xxxxxxxxx
   WORKOS_CLIENT_ID=client_xxxxxxxxx
   WORKOS_REDIRECT_URI="${APP_URL}/auth/workos/callback"
   WORKOS_CONNECT_TIMEOUT_SECONDS=10
   WORKOS_TIMEOUT_SECONDS=20
   SESSION_DRIVER=cookie
   SESSION_SECURE_COOKIE=true
   SESSION_SAME_SITE=none
   ```
   Ganti value placeholder dengan credential WorkOS asli Anda.
   Untuk env platform seperti Render, isi `WORKOS_REDIRECT_URI` dengan URL absolut (jangan pakai `${APP_URL}` literal), contoh:
   `https://wedding-planner-saas.onrender.com/auth/workos/callback`
3. Di dashboard WorkOS, pastikan Redirect URI di-set ke:
   ```text
   https://your-domain/auth/workos/callback
   ```
4. Karena migrasi auth full dan aman untuk reset data, jalankan wipe + migrate:
   ```bash
   php artisan migrate:fresh
   ```
   Jika di lokal ini Anda pakai php84:
   ```bash
   /opt/homebrew/opt/php@8.5/bin/php artisan migrate:fresh
   ```
5. Flow auth:
   - `GET /login` -> redirect ke WorkOS AuthKit (sign in)
   - `GET /register` -> redirect ke WorkOS AuthKit (sign up)
   - `GET /auth/workos/callback` -> callback login ke app
6. Flow undangan pasangan:
   - Owner kirim undangan dari fitur app -> backend membuat WorkOS User Invitation URL.
   - WorkOS akan mengirim invitation email ke pasangan.
   - Setelah pasangan accept invitation + login, callback WorkOS akan auto-attach user ke workspace yang mengundang.

## Resend API (Hello World)

1. Install Resend SDK (gunakan versi yang kompatibel dengan versi PHP Anda):
   ```bash
   composer require resend/resend-php
   ```
2. Set API key di `.env`:
   ```bash
   RESEND_API_KEY=re_xxxxxxxxx
   ```
   Ganti `re_xxxxxxxxx` dengan API key Resend asli Anda.
3. Kirim test email:
   ```bash
   php artisan resend:send-hello firstaadip16@gmail.com
   ```
