# Website Pusat Riset STEM-C USK

Laravel 13 + Filament 5 (Blade publik + panel admin CMS).

## Syarat lokal
- PHP 8.3+ (project ini memakai PHP portable di `D:\USK\php84`)
- Composer, Node.js

## Menjalankan
```powershell
$env:Path = 'D:\USK\php84;' + $env:Path
cd D:\USK\STEM\stemc-web
php artisan serve
```

- Situs publik: http://127.0.0.1:8000
- Admin panel: http://127.0.0.1:8000/admin

## Akun admin (seed)
- Email: `admin@stemc.usk.ac.id`
- Password: `password`

**Ganti password segera** setelah login pertama.

## Modul Phase 1 (sudah ada)
- Halaman Profil (Sejarah, Visi & Misi, Struktur)
- Berita & Kegiatan
- Pesan Kontak
- Pengaturan Situs
- Role: `super_admin`, `admin_konten`, `editor`
