# Dokumentasi: SSO Login Wali ERP → PWA Aplikasi

**Tanggal**: 29 Mei 2026
**Status**: ✅ LIVE & Terverifikasi
**Cakupan**: Alur login otomatis (SSO) dari Portal Wali di ERP menuju aplikasi PWA `app.asy-syifaa.com`

---

## 1. Ringkasan

Wali santri login di ERP (`erp.asy-syifaa.com`) menggunakan **nomor HP**, diarahkan ke
halaman **Portal Wali**, lalu menekan tombol **"Buka Aplikasi Sekarang"** untuk masuk
ke aplikasi PWA **tanpa login ulang** (Single Sign-On via token Sanctum).

Sebelum perbaikan, tombol tersebut hanya membawa wali ke halaman login PWA biasa
(tidak otomatis masuk). Setelah perbaikan, wali langsung mendarat di `/dashboard`.

---

## 2. Alur SSO (End-to-End)

```
1. Wali login di erp.asy-syifaa.com (login_id = nomor HP)
2. Login::authenticate() deteksi role wali → redirect ke /wali-portal
3. Portal Wali tampil → wali klik "Buka Aplikasi Sekarang"
   (link ke route auth.sso-wali)
4. SsoWaliController::redirect():
   - verifikasi user adalah wali (hasAnyRole)
   - buat token Sanctum: createToken('pwa-sso')
   - logout sesi ERP (full HTTP, aman)
   - redirect ke: app.asy-syifaa.com/login?sso_token=TOKEN
5. PWA LoginView.vue (onMounted):
   - baca query sso_token
   - simpan ke localStorage (asf_token)
   - panggil GET /api/v1/auth/me (Bearer token) untuk validasi + ambil user
   - simpan sesi (auth.setSession) → router.replace('/dashboard')
6. Wali masuk dashboard PWA. ✅
```

---

## 3. Bug yang Diperbaiki

### Bug #1 — PWA: Path API `/auth/me` ganda (penyebab utama gagal login)

- **File**: `asy-syifaa-app/pwa/src/views/auth/LoginView.vue`
- **Masalah**: `baseURL` axios sudah `https://api.asy-syifaa.com/api/v1`, tetapi handler
  SSO memanggil `api.get('/api/v1/auth/me')` → URL final `.../api/v1/api/v1/auth/me`
  → **HTTP 404** → selalu jatuh ke blok `catch` ("Sesi dari ERP tidak valid").
- **Perbaikan**: ganti menjadi `api.get('/auth/me')`.
- **Tambahan ketahanan**: `santri.fetch()` dibungkus `try/catch` agar kegagalan
  non-kritis tidak menghapus sesi yang sudah valid.

```js
// SEBELUM
const res = await api.get('/api/v1/auth/me')
// SESUDAH (baseURL sudah termasuk /api/v1)
const res = await api.get('/auth/me')
```

### Bug #2 — ERP: Role `Wali Santri` (kapital) tidak dikenali

- **File**: `erp-pesantren/app/Http/Controllers/Auth/SsoWaliController.php`
- **Masalah**: pengecekan role hanya huruf kecil (`wali_santri`, `orang_tua`, `wali`),
  padahal akun uji `wali_test` memiliki role **`Wali Santri`** (kapital). Akibatnya
  token PWA tidak pernah dibuat dan wali dipantulkan ke ERP.
- **Perbaikan**: gunakan `hasAnyRole` mencakup semua varian.

```php
// SEBELUM
$isWali = $user->hasRole('wali_santri')
       || $user->hasRole('orang_tua')
       || $user->hasRole('wali');
// SESUDAH
$isWali = $user->hasAnyRole(['wali_santri', 'orang_tua', 'wali', 'Wali Santri']);
```

### Bug #3 — UI: Banner Portal Wali menampilkan kotak putih kosong

- **File**: `erp-pesantren/resources/views/filament/pages/wali-portal.blade.php`
- **Masalah**: banner memuat `<img src="{{ asset('images/favicon.png') }}">` padahal
  folder `public/images/` kosong → icon tampil sebagai kotak putih.
- **Perbaikan**: ganti dengan heroicon SVG mandiri (`academic-cap`) warna emerald
  langsung di atas kotak putih — tidak bergantung file gambar eksternal.

---

## 4. File yang Terlibat

### ERP (`erp-pesantren`)
| File | Peran |
|------|-------|
| `app/Filament/Pages/Auth/Login.php` | Override `authenticate()` → redirect wali ke Portal Wali; field `login_id` (HP/username) |
| `app/Filament/Pages/WaliPortal.php` | Halaman Portal Wali (role-gated via `canAccess()`) |
| `app/Filament/Pages/Dashboard.php` | Kecualikan role wali agar tidak kena 403 |
| `app/Http/Controllers/Auth/SsoWaliController.php` | Buat token Sanctum + redirect ke PWA |
| `app/Http/Responses/Auth/WaliLoginResponse.php` | Fallback redirect wali (jalur non-Livewire) |
| `resources/views/filament/pages/wali-portal.blade.php` | Tampilan banner + tombol "Buka Aplikasi" |
| `app/Http/Controllers/Api/V1/AuthController.php` | Endpoint `login`, `me`, `logout` (Sanctum) |
| `routes/api.php` | Route `auth/me`, `auth/logout` di-protect `auth:sanctum` |
| `routes/web.php` | Route `auth.sso-wali` (middleware `auth:erp`) |

### PWA (`asy-syifaa-app/pwa`)
| File | Peran |
|------|-------|
| `src/views/auth/LoginView.vue` | Handler SSO `?sso_token` + form login manual |
| `src/api/client.ts` | Axios instance, `baseURL = .../api/v1`, inject Bearer token |
| `src/api/auth.ts` | `authApi.login/me/logout` (path relatif tanpa `/api/v1`) |
| `src/stores/auth.ts` | Pinia store, `setSession()` untuk SSO |
| `src/router/index.ts` | Guard `requiresAuth` / `guest` |

---

## 5. Cara Deploy

### ERP (perubahan PHP/Blade)
```bash
# di VPS — /opt/asy-syifaa/erp/src
git pull origin feature/cms-api-sso-notification
docker exec erp-app php artisan migrate --force   # jika ada migrasi baru
docker exec erp-app php artisan optimize:clear
docker restart erp-app
# untuk perubahan blade saja cukup:
docker exec erp-app php artisan view:clear
```

### PWA (perubahan Vue)
```bash
# di VPS — /opt/asy-syifaa-app
git pull origin main
docker compose build --no-cache pwa
docker compose up -d pwa
# (Vite build berjalan di dalam Docker, output ke nginx)
```

---

## 6. Verifikasi (sudah diuji live 29 Mei 2026)

| # | Langkah | Hasil |
|---|---------|-------|
| 1 | Login wali di ERP → mendarat di `/wali-portal` | ✅ |
| 2 | Klik "Buka Aplikasi Sekarang" → redirect `app.asy-syifaa.com/login?sso_token=...` | ✅ |
| 3 | PWA otomatis validasi token & masuk `/dashboard` | ✅ |
| 4 | Navigasi bottom-nav (Beranda/Keuangan/Belajar/Kegiatan/Info) | ✅ transisi mulus |
| 5 | Banner Portal Wali menampilkan icon (bukan kotak putih) | ✅ |

> Catatan: pada akun uji `wali_test` data tampil kosong ("Data santri tidak
> ditemukan") karena belum ada santri yang ter-link via `wali_account_id`.
> Ini perilaku normal, bukan bug.

---

## 7. Catatan & Tindak Lanjut

- **Konsistensi role**: disarankan menstandarkan satu slug role (mis. `wali_santri`)
  untuk menghindari kebutuhan mencocokkan banyak varian (`Wali Santri`, `wali`, dll).
- **Logout PWA → ERP**: alur logout sudah ada di sisi PWA (`authApi.logout`);
  redirect balik lintas-subdomain ke `asy-syifaa.com/login` belum dikonfigurasi.
- **HTTP 500 `POST /api/v1/auth/login`** (login manual via website) masih perlu
  investigasi terpisah — tidak memengaruhi jalur SSO yang sudah berfungsi.
