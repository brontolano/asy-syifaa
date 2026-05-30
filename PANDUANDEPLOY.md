# Panduan Deploy ERP Pesantren (Laravel 13 + Filament)

Dokumen ini adalah panduan deploy production untuk aplikasi `erp-pesantren/`.

## 1) Ringkasan Arsitektur

- Backend: Laravel 13 (PHP 8.3+)
- Frontend build: Vite
- Panel admin: Filament
- Queue: database queue (`QUEUE_CONNECTION=database`)
- Web server: Nginx + PHP-FPM
- Process manager: Supervisor (untuk queue worker)
- Scheduler: Cron (menjalankan Laravel scheduler)

## 2) Prasyarat Server

Disarankan OS: Ubuntu 22.04/24.04 LTS.

Install dependency dasar:

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx git unzip curl supervisor
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-mysql php8.3-sqlite3 php8.3-intl php8.3-gd
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
```

> Catatan: gunakan MySQL/MariaDB untuk production. SQLite di `.env.example` hanya cocok untuk local/dev.

## 3) Struktur Direktori Deploy

Contoh struktur yang rapi:

```text
/var/www/erp-pesantren
```

Clone project:

```bash
sudo mkdir -p /var/www
sudo chown -R $USER:$USER /var/www
cd /var/www
git clone <URL_REPOSITORY_ANDA> erp-pesantren
cd /var/www/erp-pesantren
```

## 4) Konfigurasi Environment

Buat file environment:

```bash
cp .env.example .env
php artisan key:generate
```

Set minimal variabel production di `.env`:

```env
APP_NAME="ERP Pesantren"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://erp.domain-pesantren.sch.id

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_pesantren
DB_USERNAME=erp_user
DB_PASSWORD=***ganti_password_kuat***

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.domain.com
MAIL_PORT=587
MAIL_USERNAME=***
MAIL_PASSWORD=***
MAIL_FROM_ADDRESS="noreply@domain-pesantren.sch.id"
MAIL_FROM_NAME="${APP_NAME}"
```

## 5) Install Dependency & Build Asset

Jalankan:

```bash
cd /var/www/erp-pesantren
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

## 6) Migrasi Database & Inisialisasi

Pastikan database sudah dibuat, lalu:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Jika belum ada kebutuhan seed di production, lewati `db:seed`.

## 7) Permission File

Set owner ke user web server:

```bash
sudo chown -R www-data:www-data /var/www/erp-pesantren
sudo find /var/www/erp-pesantren -type f -exec chmod 644 {} \;
sudo find /var/www/erp-pesantren -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/erp-pesantren/storage /var/www/erp-pesantren/bootstrap/cache
```

## 8) Konfigurasi Nginx

Buat file:

```bash
sudo nano /etc/nginx/sites-available/erp-pesantren
```

Isi konfigurasi:

```nginx
server {
    listen 80;
    server_name erp.domain-pesantren.sch.id;

    root /var/www/erp-pesantren/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan site:

```bash
sudo ln -s /etc/nginx/sites-available/erp-pesantren /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 9) Konfigurasi Queue Worker (Supervisor)

Buat file:

```bash
sudo nano /etc/supervisor/conf.d/erp-pesantren-worker.conf
```

Isi:

```ini
[program:erp-pesantren-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/erp-pesantren/artisan queue:work --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/erp-pesantren/storage/logs/worker.log
stopwaitsecs=3600
```

Reload supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start erp-pesantren-worker:*
sudo supervisorctl status
```

## 10) Konfigurasi Scheduler (Cron)

Tambahkan cron untuk user `www-data`:

```bash
sudo crontab -u www-data -e
```

Isi:

```cron
* * * * * cd /var/www/erp-pesantren && php artisan schedule:run >> /dev/null 2>&1
```

## 11) Optimasi Laravel Production

Jalankan setelah deploy:

```bash
cd /var/www/erp-pesantren
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

Jika ada perubahan konfigurasi `.env`:

```bash
php artisan optimize:clear
php artisan config:cache
```

## 12) Setup SSL (HTTPS) dengan Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d erp.domain-pesantren.sch.id
```

## 13) SOP Update Deploy (Rilis Berikutnya)

Setiap ada update kode:

```bash
cd /var/www/erp-pesantren
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart erp-pesantren-worker:*
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
```

## 14) Checklist Verifikasi Pasca Deploy

- [ ] Website bisa diakses via HTTPS
- [ ] Login admin Filament berhasil
- [ ] Migrasi database sukses (`php artisan migrate:status`)
- [ ] Queue worker status `RUNNING`
- [ ] `storage/logs/laravel.log` tidak ada error kritikal
- [ ] Upload file dan export PDF/Excel berjalan
- [ ] Notifikasi email/fitur antrean berjalan normal

## 15) Troubleshooting Cepat

1. **500 Internal Server Error**
   - Cek log: `tail -f storage/logs/laravel.log`
   - Cek permission `storage` dan `bootstrap/cache`
   - Jalankan `php artisan optimize:clear`

2. **Asset CSS/JS tidak muncul**
   - Jalankan ulang `npm run build`
   - Pastikan folder `public/build` terisi

3. **Queue tidak jalan**
   - Cek `sudo supervisorctl status`
   - Restart worker supervisor

4. **Perubahan `.env` tidak terbaca**
   - Jalankan: `php artisan optimize:clear && php artisan config:cache`

---

## 16) Gambaran Sistem (Hasil Pembacaan Kode)

- **Stack Utama**
  - Laravel **13.x** dengan PHP **8.3+** (lihat `composer.json`).
  - Panel admin berbasis **Filament 5** sebagai antarmuka utama ERP.
  - Role & permission memakai **Spatie Laravel Permission** (guard `erp`).
  - API dan portal memakai **Laravel Sanctum** untuk autentikasi berbasis token/cookie.
  - Cetak dokumen memakai **laravel-dompdf**, import Excel memakai **PhpSpreadsheet**.
- **Autentikasi & Guard**
  - Guard utama ERP adalah `erp` (driver `session`, provider `erp_accounts`).
  - Model akun: `ErpAccount` (punya role, notifikasi, soft delete, dan bisa login ke Filament).
  - Filament panel dikonfigurasi menggunakan `authGuard('erp')` dan path `/`, sehingga domain ERP langsung membuka panel login/dashboard.

## 17) Modul & Fitur Utama ERP

- **SPMB / PPDB Online**
  - Pendaftaran calon santri via endpoint API publik, data masuk ke model `PpdbRegistration` beserta `PpdbDocument`.
  - Service `SpmbService` menangani alur:
    - Registrasi, upload dan verifikasi dokumen,
    - Penetapan hasil seleksi,
    - Pembuatan tagihan daftar ulang,
    - Konversi pendaftar menjadi santri aktif.
  - Di panel Filament tersedia:
    - Resource untuk mengelola pendaftar, dokumen, seleksi,
    - Portal pendaftar (`Dokumen Saya`, `Tagihan Saya`, `Profil Saya`, dashboard, ujian, hasil seleksi).

- **Keuangan Pesantren**
  - Model keuangan utama:
    - `BillingType`, `HijriBillingPeriod`, `Invoice`, `InvoiceItem`, `Payment`, `PaymentMethod`, `PaymentProof`, `WaqofLog`.
  - Fitur:
    - Konfigurasi jenis tagihan (SPP, daftar ulang, ujian, dll).
    - Penagihan syahriyyah per periode Hijriah.
    - Pencatatan pembayaran, upload bukti transfer, approval, dan laporan keuangan.
  - Console command penting:
    - `php artisan keuangan:generate-spp` untuk generate tagihan SPP bulanan (opsi cek tunggakan dan auto-waqof).
    - `php artisan santri:import` untuk import master santri dan sekaligus setup billing awal.

- **Kepesantrenan (Data & Administrasi Santri)**
  - Model `Student` menyimpan biodata santri dan wali, status (aktif, waqof, alumni, dll), dan relasi dengan akun ERP.
  - Fitur:
    - Import/export santri via Excel,
    - Pengelompokan berdasarkan jenjang, kelas, dan tahun masuk,
    - Manajemen alumni dan log waqof.

- **User Management & Portal Akun**
  - `ErpAccount` menyimpan semua akun (superadmin, admin, mudir, guru, wali santri, santri, pendaftar, dst).
  - Seeder `RoleSeeder` menyiapkan banyak role dengan guard `erp`.
  - Di panel tersedia resource untuk:
    - Manajemen akun ERP,
    - Melihat dan mengubah profil sendiri,
    - Mengganti password (dengan mekanisme wajib ganti password untuk akun tertentu).

- **Pengaturan & Template**
  - Model seperti `AppSetting`, `PaymentMethod`, `LetterHeader`, `SuratTemplate`, `WebhookLog`.
  - Di panel terdapat halaman untuk:
    - Konfigurasi metode pembayaran dan rekening resmi pesantren,
    - Pengaturan header surat dan template surat,
    - Manual pengguna dan halaman backup/restore.

## 18) Dependensi & Service Pendukung (Berdasarkan Kode)

- **Database**
  - `.env.example` mengarah ke `DB_CONNECTION=sqlite` untuk pengembangan lokal.
  - File arsitektur menyarankan **PostgreSQL** untuk produksi; sesuaikan di `.env`:
    - `DB_CONNECTION=pgsql`
    - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

- **Queue**
  - Default queue di `.env.example` adalah `QUEUE_CONNECTION=database`.
  - Konfigurasi `config/queue.php` menyiapkan koneksi `database` dengan tabel `jobs`, `failed_jobs`, `job_batches`.
  - Script `composer dev` menjalankan `php artisan queue:listen`, sehingga di produksi wajib ada worker yang berjalan terus-menerus.

- **Cache & Session**
  - `.env.example`:
    - `SESSION_DRIVER=database`
    - `CACHE_STORE=database`
  - Bisa dioptimalkan ke Redis di produksi jika diperlukan throughput lebih besar.

- **Integrasi Eksternal (N8N + WAHA)**
  - Service `WebhookNotificationService` mengirim webhook ke N8N, yang kemudian meneruskan ke WAHA (WhatsApp).
  - Event untuk SPMB dan pembayaran memicu notifikasi otomatis (contoh: pendaftar baru, dokumen ditolak, semua dokumen lengkap, hasil seleksi, pembayaran diterima).
  - URL dan credential webhook dibaca dari konfigurasi dan `.env` (misal `SPMB_WEBHOOK_URL`, `SPMB_PORTAL_URL`).

## 19) Proses Latar Belakang (Queue & Scheduler)

- **Event & Listener**
  - Beberapa event domain penting:
    - `SpmbRegistered`, `DocumentVerified`, `AllDocumentsVerified`, `SelectionDecided`, `DaftarUlangPaid`, `PaymentProofApproved`.
  - Listener menggunakan queue untuk:
    - Mengirim notifikasi ke WA/N8N,
    - Mengubah status domain (contoh: auto-konversi pendaftar ke santri setelah bayar daftar ulang).

- **Queue Worker di Produksi**
  - Minimal satu proses worker yang selalu hidup:
    - Contoh (Supervisor): `php /var/www/erp-pesantren/artisan queue:work --sleep=3 --tries=3 --timeout=120`.
  - Worker ini yang memastikan notifikasi, broadcast penagihan, dan proses latar belakang lain berjalan konsisten.

- **Scheduler / Cron**
  - Dari pembacaan kode, belum ada jadwal bawaan di `Console Kernel` yang memanggil command keuangan otomatis.
  - Rekomendasi:
    - Pakai cron server untuk memanggil:
      - `php artisan schedule:run` bila nantinya Kernel diisi jadwal, **atau**
      - `php artisan keuangan:generate-spp` sesuai jadwal kalender Hijriah yang disepakati.

## 20) Catatan Khusus Deploy Berdasarkan Kode

- **Setelah Deploy Pertama Kali**
  - Pastikan:
    - `.env` sudah diisi lengkap (APP, DB, MAIL, QUEUE, WEBHOOK/N8N).
    - `php artisan migrate --force` sudah sukses.
    - Seeder role sudah dijalankan (minimal `RoleSeeder`).
    - Minimal satu akun `ErpAccount` dengan role tinggi (misal `Superadmin`) sudah dibuat.
  - Uji:
    - Login panel ERP di `/`.
    - Jalankan satu alur SPMB dummy,
    - Jalankan generate SPP di environment staging,
    - Uji integrasi WA via N8N.

- **Update Versi / Rilis Baru**
  - Ikuti langkah di bagian SOP update (section 13) lalu tambah:
    - Jika ada perubahan pada struktur data santri atau keuangan, sesuaikan juga proses import dan command keuangan (`santri:import`, `keuangan:generate-spp`).
    - Cek kembali permission Filament (role yang boleh mengakses menu tertentu) bila ada penambahan modul.

---

Dokumen ini menyatukan **langkah teknis deploy** dan **gambaran fungsional sistem** berdasarkan pembacaan folder `erp-pesantren/`. Silakan sesuaikan nama domain, konfigurasi database, dan integrasi eksternal sesuai lingkungan server pesantren.
