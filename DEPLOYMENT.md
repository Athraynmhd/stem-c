# Deployment Log — stem.lppm.usk.ac.id

Dokumen ini mencatat **persis** apa yang dilakukan saat pertama kali men-deploy `stemc-web` ke hosting Plesk `maleo.usk.ac.id`, termasuk kredensial yang dibuat, kenapa struktur foldernya tidak standar, dan cara redeploy/update di kemudian hari. Ditulis untuk developer berikutnya yang mungkin belum pernah pegang server ini.

Tanggal deploy pertama: **26 Agustus 2026**.

---

## 1. Ringkasan infrastruktur

| Item | Nilai |
|---|---|
| Panel hosting | Plesk Obsidian 18.0.80 |
| URL panel | https://maleo.usk.ac.id:8443 |
| Login panel | `stem.lppm.usk.ac.id` |
| Domain situs | https://stem.lppm.usk.ac.id |
| PHP | 8.3.33 (fixed, tidak ada pilihan versi lain yang dikonfirmasi tersedia) |
| Web server | Apache/nginx di belakang Plesk, FastCGI |
| Database | MariaDB 10.11.14, host `localhost:3306` |
| SSH | **Forbidden** (dicek di Hosting Settings → SSH access, statusnya "Forbidden", tidak bisa diaktifkan sendiri) |
| Shell exec / proc_open | **Tidak berfungsi normal** — script apapun yang butuh spawn subprocess (composer post-install scripts seperti `artisan package:discover`, `artisan filament:upgrade`) gagal dengan error `Process class relies on proc_open` |
| symlink() | **Berfungsi normal** (sempat dikira disabled, ternyata tidak — lihat §5.4) |
| Document root per-domain | **Disabled/terkunci** di level akun — field ini tidak bisa diubah lewat UI Plesk maupun lewat manipulasi DOM/JS (dicek: `disabled: true` di atribut HTML-nya, bukan sekadar readonly). Ini kebijakan hosting, bukan bug. |

---

## 2. Kredensial yang dibuat/diubah saat deploy ini

**PENTING: semua password di bawah ini harus dianggap bocor** (tercatat di riwayat chat/dokumen ini) dan sebaiknya **diganti** jika keamanan jadi concern, meski risikonya rendah karena hanya bisa dipakai lewat panel Plesk/FTP yang butuh akses jaringan ke server ini.

### 2.1 Login Plesk (sudah ada sebelumnya, tidak diubah)
- User: `stem.lppm.usk.ac.id`
- Password: (yang diberikan user di awal sesi — tidak diulang di sini, lihat riwayat chat kalau perlu)
- **Catatan penting**: password Plesk **≠** password FTP/system-user. Ini dua kredensial terpisah di Plesk meski usernamenya sama persis. Ini yang bikin upload FTP pertama gagal (530 Access denied) — sempat dikira salah ketik, ternyata memang beda field di Plesk (`Hosting Settings → Webspace settings → System user's credentials`).

### 2.2 FTP / System user password — **DIGANTI** saat deploy ini
- Lokasi ubah: Plesk → Websites & Domains → `stem.lppm.usk.ac.id` → Hosting Settings → Webspace settings → System user's credentials → tombol "Generate"
- Username: `stem.lppm.usk.ac.id` (sama seperti login Plesk)
- Password baru: `YOUR_FTP_PASSWORD`
- Kenapa diganti: password Plesk yang diberikan user gagal dipakai untuk FTP (ProFTPD menolak dengan 530). Daripada menebak-nebak, langsung generate password FTP baru khusus lewat panel.
- Host FTP: `103.41.169.176`, port `21`, server `ProFTPD`

### 2.3 Database MySQL — **DIBUAT** saat deploy ini
- Nama database: `stemc_web`
- Username: `stemc_web`
- Password: `^Wo!T3Xu67gwmwbi`
- Host: `localhost`, port `3306`
- Dibuat lewat: Plesk → Databases → Add Database

### 2.4 Akun admin aplikasi (Filament panel)
- URL login: https://stem.lppm.usk.ac.id/admin
- Email: `admin@stemc.usk.ac.id`
- Password: `password`
- Sumber: ini password default yang **sudah ada di source code** (`database/seeders/DatabaseSeeder.php`), bukan sesuatu yang dibuat khusus untuk production. **Wajib ganti setelah login pertama** — password ini bisa dilihat siapa saja yang punya akses ke repo.

---

## 3. Kenapa struktur foldernya tidak standar Laravel

Struktur Laravel normal:
```
project-root/
├── app/
├── bootstrap/
├── public/          ← document root diarahkan ke sini
├── vendor/
└── artisan
```

Yang dipakai di server ini:
```
Home directory (subscription root, di File Manager Plesk disebut "Home directory")
├── app/
├── bootstrap/
├── config/
├── database/
├── lang/
├── resources/
├── routes/
├── storage/
├── tests/
├── vendor/
├── artisan
├── composer.json / composer.lock
├── .env
├── package.json / vite.config.js / dll
│
└── httpdocs/        ← INI document root (fixed, tidak bisa diubah)
    ├── index.php     (Laravel front controller, biasanya di public/)
    ├── .htaccess
    ├── robots.txt
    ├── favicon.ico
    ├── build/        (hasil `npm run build`, biasanya public/build)
    ├── storage/      (symlink ke ../storage/app/public, dibuat oleh `artisan storage:link`)
    ├── js/ fonts/ css/ assets/   (asset statis lain yang tadinya di public/)
    └── tmp/          (folder bawaan Plesk, sudah ada sebelum deploy, jangan dihapus)
```

**Kenapa begini:** field "Document root" di Plesk Hosting Settings untuk domain ini disabled (dikonfirmasi lewat inspeksi DOM: `<input id="hostingSettings-documentRoot" disabled>`), jadi tidak mungkin diarahkan ke `httpdocs/public`. Solusinya: isi folder `public/` Laravel dipindah **langsung** ke `httpdocs/` (jadi `httpdocs` itu sendiri yang berfungsi sebagai `public/`), dan semua source code lain (yang tadinya sejajar dengan `public/`) dipindah satu level ke atas, ke "Home directory" (parent dari `httpdocs`).

Ini otomatis memenuhi syarat Laravel: `index.php` mencari `../vendor/autoload.php` dan `../bootstrap/app.php` lewat path relatif — karena `httpdocs` adalah child langsung dari Home directory yang berisi `vendor/` dan `bootstrap/`, path relatif itu tetap valid tanpa perlu diubah sama sekali.

**Konsekuensi penting untuk developer berikutnya:**
- `public_path()` Laravel secara default menghitung `base_path('public')`, yang **tidak ada** di server ini (foldernya bernama `httpdocs`, bukan `public`, dan levelnya beda). Untuk mengatasi ini, setiap script yang bootstrap Laravel manual (lihat §5) harus memanggil:
  ```php
  $app = require_once __DIR__ . '/../bootstrap/app.php';
  $app->usePublicPath(__DIR__); // __DIR__ di sini = folder tempat script itu berada = httpdocs
  ```
  **Idealnya**, baris `$app->usePublicPath(...)` ini ditambahkan permanen di `httpdocs/index.php` (bukan cuma di script sementara), supaya semua request normal ke situs juga menghitung `public_path()` dengan benar (dipakai misalnya oleh Vite asset helper, `Storage::disk('public')`, dll). **Cek dulu apakah `index.php` di server sudah punya baris ini** sebelum redeploy — kalau belum, tambahkan.
- Laravel Toolkit bawaan Plesk (menu "Laravel" di sidebar) **tidak bisa dipakai** untuk kelola aplikasi ini — fitur "Scan"-nya mensyaratkan folder bernama literal `public` sebagai document root, dan document root tidak bisa diarahkan ke situ. Jangan buang waktu coba "Scan" lagi, itu akan selalu melaporkan "0 applications found".

---

## 4. Kendala teknis yang ditemukan (dan solusinya)

### 4.1 BOM di `package.json` bikin `npm run build` gagal
`package.json` di root project ternyata punya UTF-8 BOM (`EF BB BF`) di byte pertama. Ini bikin `postcss-load-config` gagal parse file itu sebagai JSON saat vite build jalan (error: `Unexpected token '﻿'`). Fix: hapus BOM-nya.
```bash
sed -i '1s/^\xef\xbb\xbf//' package.json
```
Kalau build gagal lagi dengan error serupa, cek dulu apakah ada BOM di file JSON manapun yang ikut diproses.

### 4.2 `composer.lock` di-lock untuk PHP 8.4, server cuma ada PHP 8.3
Lokal dev pakai PHP portable yang ternyata versi 8.4 (folder `D:\USK\php84`), jadi `composer.lock` hasil `composer install` lokal berisi resolusi paket (terutama sub-dependency `symfony/*`) yang butuh `php >=8.4.1`. Server production cuma ada PHP 8.3.33. Composer di server gagal install dengan pesan "Your lock file does not contain a compatible set of packages."

**Fix:** jangan upload `composer.lock` yang di-generate di lokal kalau versi PHP lokal beda dari server. Di Plesk PHP Composer tool, klik **"Update"** (bukan "Install") supaya composer re-resolve dependency yang kompatibel dengan PHP di server, lalu commit ulang `composer.lock` yang baru itu ke repo supaya deploy berikutnya konsisten.

### 4.3 Composer post-install scripts gagal (`proc_open` bermasalah)
Script Composer di `composer.json` (`post-autoload-dump`, dll) menjalankan `@php artisan package:discover --ansi` dan `@php artisan filament:upgrade` lewat subprocess. Di server ini itu gagal dengan:
```
In Process.php line 147:
The Process class relies on proc_open, which is not available on your PHP installation.
```
Ini **tidak fatal** — package installation & autoload generation tetap sukses, cuma langkah "package discovery cache" dan "filament upgrade" yang gagal. Laravel tetap jalan normal karena package manifest di-generate lazy saat runtime kalau belum ada cache-nya. Tapi kalau ada perintah artisan baru yang perlu dijalankan setelah composer update (migrasi baru, publish asset baru, dll), harus dijalankan manual lewat teknik di §5.

### 4.4 Folder cache Laravel tidak ada di zip
Untuk memperkecil ukuran upload, saat bikin zip project lokal, folder-folder ini sengaja di-exclude:
```
storage/framework/cache/data
storage/framework/sessions
storage/framework/views
storage/framework/testing
```
Tapi Laravel butuh folder-folder ini **ada** (boleh kosong) supaya bisa nulis file cache/session/compiled-view. Kalau tidak ada, semua command yang sentuh cache (`migrate`, `config:cache`, dll) gagal dengan error **"Please provide a valid cache path."**

**Fix:** selalu buat folder-folder itu (kalau belum ada) sebelum menjalankan artisan command apapun di server. Lihat contoh script di §5.

### 4.5 `storage:link` gagal dua kali dengan alasan berbeda
- **Percobaan 1** — error `symlink(): No such file or directory`. Penyebab: `config:cache` yang **lama** (dari percobaan sebelumnya, sebelum `usePublicPath()` ditambahkan) masih ke-load duluan karena command `storage:link` dijalankan **sebelum** `config:clear` dalam urutan command. Config lama itu berisi path link yang salah (masih menghitung `public_path()` default, bukan `httpdocs`).
  **Fix:** urutan command yang benar adalah **clear cache dulu** (`config:clear`, `cache:clear`, `route:clear`, `view:clear`), **baru** `migrate` dan `storage:link`, dan `config:cache` **paling akhir**.
- **Percobaan 2** — error `symlink(): File exists`. Penyebab: ada folder asli (bukan symlink) bernama `httpdocs/storage` yang berisi `.gitignore` + `site/` — ini duplikat dari `storage/app/public` yang **ikut ter-zip** karena waktu bikin zip di Windows, folder `public/storage` yang seharusnya symlink (dibuat oleh `php artisan storage:link` di lokal) malah didereferensi jadi folder biasa oleh `zip` (Windows tidak selalu menangani symlink dengan benar saat di-zip via git-bash `zip -r`).
  **Fix:** hapus folder `httpdocs/storage` itu (permanen, skip recycle bin) sebelum jalankan `storage:link` lagi.

**Pelajaran untuk redeploy:** jangan zip `public/storage` dari lokal sama sekali (selalu `-x "public/storage/*"` di command zip), biarkan `storage:link` yang bikin symlink-nya langsung di server. Isi file upload asli tetap aman karena sumber sebenarnya ada di `storage/app/public/`, bukan di `public/storage/` (yang cuma symlink).

### 4.6 `DatabaseSeeder` sengaja diblokir di production
```php
if (app()->isProduction()) {
    throw new RuntimeException(
        'DatabaseSeeder tidak boleh dijalankan di produksi: berisi akun dan konten contoh.',
    );
}
```
Ini guard yang **sengaja** ditulis developer sebelumnya karena `DatabaseSeeder` isinya bukan cuma akun admin, tapi juga puluhan record dummy (berita palsu, peneliti palsu, publikasi palsu, dll — lihat isi lengkap `database/seeders/DatabaseSeeder.php`).

Untuk deploy pertama ini, atas keputusan user, seeder **tetap dijalankan** (dengan trik override `APP_ENV=local` sementara lewat `putenv()`, tanpa mengubah file `.env` produksi) supaya situs langsung ada isi contoh yang bisa diedit admin. Detail teknik ada di §5.3.

**Kalau redeploy dari nol di masa depan** dan tidak mau ada data dummy lagi: jangan panggil `db:seed` sama sekali, cukup buat 1 user admin manual (lihat §5.3, bagian bawah, ada contoh kode untuk itu tanpa ikut seed data dummy).

---

## 5. Cara kerja "PHP bridge" untuk jalankan artisan command

Karena SSH forbidden dan `proc_open`/`shell_exec` bermasalah untuk sub-process, **tidak bisa** menjalankan `php artisan migrate` dkk secara normal. Solusinya: buat file `.php` sementara di `httpdocs/` (document root) yang **langsung** bootstrap Laravel di dalam proses PHP yang sama (tanpa spawn subprocess apapun), lalu akses file itu lewat browser/curl, lalu **hapus filenya** setelah selesai (supaya tidak jadi backdoor kalau lupa).

### 5.1 Template dasar
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(180);

$base = dirname(__DIR__); // Home directory, parent dari httpdocs

// Pastikan semua folder cache Laravel ada (lihat §4.4)
$dirs = [
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/framework/testing',
    'storage/app/public',
    'storage/logs',
    'bootstrap/cache',
];
foreach ($dirs as $dir) {
    $full = $base . '/' . $dir;
    if (!is_dir($full)) {
        mkdir($full, 0775, true);
    }
    chmod($full, 0775);
}

header('Content-Type: text/plain');

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->usePublicPath(__DIR__); // WAJIB, lihat §3
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Urutan PENTING: clear dulu, baru migrate/storage:link, config:cache paling akhir
$commands = [
    'config:clear' => [],
    'cache:clear' => [],
    'route:clear' => [],
    'view:clear' => [],
    'migrate' => ['--force' => true],
    'storage:link' => [],
    'config:cache' => [],
];

foreach ($commands as $cmd => $args) {
    echo "=== $cmd ===\n";
    try {
        $kernel->call($cmd, $args);
        echo $kernel->output();
    } catch (Throwable $e) {
        echo 'ERROR: ' . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "DONE\n";
```

### 5.2 Cara pakai
1. Buka Plesk File Manager → `httpdocs` → Create File → beri nama misalnya `_deploy.php`.
2. Klik file itu → edit lewat Code Editor bawaan Plesk → paste isi di atas → Save.
3. Buka `https://stem.lppm.usk.ac.id/_deploy.php` di browser, baca outputnya (plain text).
4. **Hapus file itu segera** setelah selesai (File Manager → select → Remove → centang "Skip the Recycle Bin and permanently remove").

### 5.3 Cara bikin 1 user admin **tanpa** ikut seed data dummy (untuk redeploy production yang bersih)
Kalau di masa depan mau redeploy dari database kosong dan **tidak** mau ada konten contoh (berbeda dari keputusan di deploy pertama ini), pakai script ini menggantikan `db:seed`:

```php
<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->usePublicPath(__DIR__);
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');

foreach (['super_admin', 'admin_konten', 'editor'] as $role) {
    \Spatie\Permission\Models\Role::findOrCreate($role);
}

$admin = \App\Models\Auth\User::query()->updateOrCreate(
    ['email' => 'GANTI_DENGAN_EMAIL_ADMIN'],
    [
        'name' => 'GANTI_DENGAN_NAMA',
        'password' => \Illuminate\Support\Facades\Hash::make('GANTI_DENGAN_PASSWORD_KUAT'),
    ],
);
$admin->syncRoles(['super_admin']);

echo "Admin dibuat: {$admin->email}\n";
```
Ganti tiga placeholder di atas, jalankan sekali lewat cara di §5.2, lalu **hapus filenya**.

### 5.4 Kalau `storage:link`/symlink dicurigai gagal karena symlink disabled
Sempat dikira `symlink()` di-disable di server ini (mirip `proc_open`). Ternyata **tidak** — dicek lewat `ini_get('disable_functions')`, cuma `opcache_get_status` yang disabled. Kalau `storage:link` gagal, kemungkinan besar penyebabnya salah satu dari dua hal di §4.5, bukan symlink dibatasi hosting. Diagnostik cepat:
```php
<?php
header('Content-Type: text/plain');
echo "disable_functions: " . ini_get('disable_functions') . "\n";
echo "symlink exists: " . (function_exists('symlink') ? 'yes' : 'no') . "\n";
```

---

## 6. Cara redeploy / update situs setelah ada perubahan kode

Alur untuk update kode yang sudah ada (bukan setup pertama kali):

1. **Build asset di lokal** kalau ada perubahan di `resources/` (CSS/JS):
   ```bash
   npm run build
   ```
2. **Bikin zip** dari project lokal, **exclude** hal-hal ini:
   ```
   node_modules/*
   vendor/*          (composer install ulang di server, lebih aman untuk kompatibilitas PHP)
   .env              (jangan pernah timpa .env production)
   .git/*
   public/storage/*  (itu symlink lokal, jangan ikut di-zip — lihat §4.5)
   storage/framework/cache/*
   storage/framework/sessions/*
   storage/framework/views/*
   storage/framework/testing/*
   ```
3. **Upload via FTP** (bukan lewat File Manager kalau ukurannya di atas ~10MB — File Manager Plesk ada limit upload lewat browser):
   ```bash
   curl --ftp-method nocwd -T project-update.zip \
     "ftp://103.41.169.176/httpdocs/project-update.zip" \
     --user 'YOUR_FTP_USERNAME'
   ```
   (Ganti YOUR_FTP_USERNAME dengan username FTP dari pengaturan hosting. Masukkan password saat diminta oleh curl. Jangan menyimpan password di repository.)
4. **Extract** lewat File Manager Plesk: pilih file zip → Archive → Extract Files.
   - **Ingat struktur non-standar (§3)**: isi yang tadinya di `public/` harus masuk ke `httpdocs/` langsung, dan isi lainnya (app, config, dll) harus masuk ke Home directory (parent `httpdocs`), **bukan** ke dalam `httpdocs`. Kalau extract langsung ke `httpdocs`, harus dipindah manual pakai Move di File Manager mengikuti pola yang sama seperti di §3.
5. **Composer install/update** lewat Plesk → domain → PHP Composer (klik "Update" kalau ada perubahan `composer.json`/lock, "Install" kalau tidak).
6. **Jalankan artisan command yang perlu** (migrate untuk migration baru, dll) lewat teknik PHP-bridge di §5. Urutan command selalu: clear cache dulu → migrate/perintah lain → config:cache di akhir.
7. **Hapus file bridge PHP** setelah selesai.
8. Test situs & panel admin di browser.

---

## 7. Rekomendasi tindak lanjut

Status per **26 Agustus 2026**:

- [ ] **Ganti password admin Filament** (`admin@stemc.usk.ac.id` / `password`) — prioritas tinggi, password ini ada di source code publik. Belum dilakukan, ini tugas user (bukan sesuatu yang bisa dikerjakan lewat deploy).
- [ ] **Set `MAIL_MAILER` yang asli** di `.env` — saat ini masih `log`, artinya form kontak di situs **tidak benar-benar mengirim email**, cuma dicatat ke `storage/logs/laravel.log`. Menunggu kredensial SMTP dari user (host/port/username/password mail yang mau dipakai).
- [ ] **Google Analytics/GTM ID** — field-nya sudah ada di panel admin (Identitas & Kontak → SEO dasar), menunggu ID dari user kalau sudah punya akun GA/GTM.
- [ ] **Backup ke remote storage** — backup otomatis harian **sudah jalan** (dijadwalkan admin server USK), tapi "Remote Storage Settings" di Plesk Backup Manager masih "Not configured" untuk FTP(S) maupun Google Drive — artinya backup cuma tersimpan di server yang sama, hilang bareng kalau disk server bermasalah. Menunggu keputusan user (mau pakai Google Drive? perlu login OAuth Google milik user sendiri, tidak bisa diwakilkan).
- [ ] **Minta akses SSH/Git ke admin USK** — supaya deploy berikutnya tidak perlu lagi trik FTP + PHP-bridge di dokumen ini. Perlu user yang mengajukan ke pengelola server (`maleo.usk.ac.id`), bukan sesuatu yang bisa diminta dari sisi customer/Plesk subscription ini.
- [x] Tambahkan `$app->usePublicPath(__DIR__);` di `httpdocs/index.php` — **cek ini masih perlu dilakukan** kalau file `index.php` di server belum diedit manual sejak dokumen ini ditulis (lihat §3, catatan penting soal `public_path()`).

---

## 8. Update 26 Agustus 2026 — Ganti logo & perbaikan kecil

### 8.1 Ganti logo situs (favicon, header, panel admin, preloader)
Logo lama (ikon orbit sederhana + teks "STEM-C") diganti dengan logo baru "STEM.id" (bunga kuning UNSYIAH + wordmark warna-warni), dari file sumber `D:\USK\STEM\stem_id.png` (2853×772, PNG transparan).

**Temuan penting:** ternyata *semua* tempat logo tampil (header situs terang & gelap, sidenav, panel login Filament, brand logo admin panel) sudah memakai **4 file yang sama**, jadi cukup timpa isinya tanpa ubah kode maupun database sama sekali:

| File | Dipakai untuk | Isi baru |
|---|---|---|
| `public/assets/images/logo/logo-stemc-light.svg` | Logo di atas hero gelap (`chrome['logoLight']`) | SVG pembungkus, isi `<image>` base64 dari logo penuh (2853×772) |
| `public/assets/images/logo/logo-stemc-dark.svg` | Logo saat menu jadi putih/sticky, sidenav, mobile nav, **dan** brand logo + favicon panel admin Filament (hardcode di `AdminPanelProvider.php`) | Sama persis dengan file di atas |
| `public/assets/images/loader-stemc.svg` | Animasi preloader saat halaman dimuat | SVG pembungkus, isi `<image>` base64 dari crop badge bunga kuning saja (persegi, dari `og-default` crop) |
| `public/assets/images/fav-icon/icon.png` | Favicon tab browser (`chrome['favicon']`) | PNG 256×256, crop badge bunga kuning saja |

**Kenapa favicon & preloader di-crop, bukan pakai logo penuh:** logo sumber itu wide/landscape (rasio ~3.7:1, ada teks "STEM.id Learning is Fun" di sebelah kanan badge). Favicon browser tab cuma 16–32px persegi — kalau logo penuh dipaksa masuk situ hasilnya smear tidak kebaca. Jadi favicon & preloader cuma pakai bagian **badge bunga kuning** (kolom kiri gambar sumber, crop `(0,0,772,772)` dari PNG asli), sedangkan header/panel admin tetap pakai logo penuh karena ruangnya cukup lebar.

**Teknik SVG-wrapper:** karena kode di seluruh proyek mereferensikan nama file `.svg` secara eksplisit (bukan `.png`), file PNG baru tidak bisa langsung menggantikan file `.svg` — jadi PNG di-encode base64 lalu dibungkus dalam elemen `<svg><image xlink:href="data:image/png;base64,...."/></svg>` dengan `width`/`height`/`viewBox` yang meniru ukuran SVG lama (`146×44` untuk logo header, `32×32` untuk preloader). Ini valid HTML/SVG standar, browser merender `<image>` di dalam `<svg>` tanpa masalah. Konsekuensi: ukuran file jadi lebih besar dari SVG vector asli (±278 KB untuk logo header, karena isinya raster PNG yang di-base64), tapi untuk situs kecil seperti ini dampaknya tidak signifikan.

**Script generate asetnya** (pakai Python + Pillow, dijalankan lokal sebelum upload):
```python
from PIL import Image
import base64, io

src = Image.open(r'D:\USK\STEM\stem_id.png')  # sumber logo asli

# Crop badge (kiri, persegi) untuk favicon & preloader
icon = src.crop((0, 0, 772, 772)).resize((256, 256), Image.LANCZOS)
icon.save('.../fav-icon/icon.png', format='PNG', optimize=True)

# Logo penuh untuk header (base64 dibungkus SVG, lihat isi lengkap di riwayat commit/chat)
```

Kalau logo diganti lagi di masa depan, ulangi pola yang sama: crop badge persegi untuk favicon/preloader, base64-encode logo penuh untuk 2 file `logo-stemc-*.svg`, lalu upload lewat FTP menimpa nama file yang sama persis (tidak perlu ubah kode).

**Cache browser:** setelah upload, tampilan lama sempat masih muncul di browser yang sudah pernah buka situs sebelumnya — itu cache gambar sisi klien (nama file tidak berubah, jadi browser reuse cache lama). Hard refresh (`Ctrl+Shift+R`) di sisi kita mengonfirmasi filenya sudah benar; pengunjung asli akan otomatis dapat versi baru begitu cache lokal mereka kedaluwarsa atau mereka hard-refresh sendiri. Kalau ingin instan untuk semua orang, perlu ubah nama file (cache-busting) dan update semua referensi kode — sengaja tidak dilakukan di sini karena tidak worth kompleksitasnya untuk kasus ini.

### 8.2 Security headers dasar
Ditambahkan ke `public/.htaccess`, di dalam blok `<IfModule mod_headers.c>` yang sudah ada:
```apache
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```
**Sengaja tidak menambahkan Content-Security-Policy** — tema Bootstrap yang dipakai situs ini banyak memakai inline `<script>`/`<style>`, CSP yang ketat kemungkinan besar mematahkan tampilan tanpa audit terpisah dulu untuk mendaftar semua sumber yang perlu di-whitelist.

### 8.3 Open Graph & Twitter Card image
`resources/views/layouts/site.blade.php` sudah punya `og:title`, `og:description`, `og:type`, `og:url`, tapi **belum ada `og:image`** — artinya kalau link situs dibagikan ke WhatsApp/medsos, tidak muncul gambar preview. Ditambahkan:
- Aset baru `public/assets/images/og-default.png` (1200×630, logo baru di kanvas putih, dibuat dengan Pillow supaya proporsional bukan transparan penuh — kanvas putih dipilih karena beberapa platform merender PNG transparan dengan background putih otomatis, jadi hasilnya konsisten).
- Meta tag `og:image`, `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image` di `<head>`.
- Halaman spesifik (misal halaman berita dengan gambar sampul) bisa override lewat `@section('og_image', $news->cover_url)` sebelum `@extends` layout — pola ini sudah dipakai untuk `og_type`, tinggal ikuti pola yang sama. **Belum diterapkan** di halaman berita/publikasi individual, cuma default sitewide yang sudah aktif.

### 8.4 Terjemahan EN — dicek, tidak ada perubahan kode
Dicek apakah toggle bahasa ID/EN di header berfungsi penuh. Temuan: `lang/en/nav.php` dan `lang/id/nav.php` sudah lengkap dan sinkron (semua label menu). Model konten (`News`, `Page`, dll via trait `HasOptionalLocale`) sudah punya mekanisme kolom `_en` opsional dengan fallback otomatis ke bahasa Indonesia kalau kosong. Karena konten yang ada sekarang cuma data seed/dummy, kolom `_en`-nya masih kosong — ini **bukan bug**, itu perilaku fallback yang disengaja. Begitu admin mengisi versi Inggris tiap konten lewat panel, otomatis akan tampil saat locale EN dipilih. Tidak ada perubahan kode di bagian ini.

### 8.5 Backup — dicek, tidak ada perubahan
Dicek Plesk Backup Manager: ternyata sudah ada jadwal backup harian otomatis (dikonfigurasi di level admin server USK, bukan sesuatu yang kita atur). 3 backup harian tersimpan per tanggal dokumen ini ditulis. **Remote Storage Settings masih "Not configured"** untuk FTP(S) dan Google Drive — backup cuma tersimpan lokal di server yang sama. Belum ditindaklanjuti, menunggu keputusan user (lihat §7).
