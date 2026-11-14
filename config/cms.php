<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Editor Gambar Inline
    |--------------------------------------------------------------------------
    |
    | Mengganti gambar langsung dari halaman publik memang praktis, tetapi
    | permukaan serangnya lebih lebar daripada panel admin: berkas diunggah
    | dari halaman yang juga dilihat pengunjung. Semua pengetatan di bawah ini
    | bisa diatur tanpa menyentuh kode.
    |
    */

    'inline_edit' => [

        /*
        | Sakelar utama. Mati secara bawaan di produksi supaya penyalaannya
        | harus disengaja lewat CMS_INLINE_EDIT=true. Saat mati, penanda tidak
        | dicetak di HTML dan endpoint unggah menolak semua permintaan.
        */
        'enabled' => (bool) env('CMS_INLINE_EDIT', env('APP_ENV', 'production') !== 'production'),

        /*
        | Hanya role ini yang mendapat editor inline. Role lain tetap bisa
        | mengurus gambar dari dashboard sesuai matriks App\Cms\Access\CmsAccess;
        | daftar ini mempersempit, tidak pernah memperluas, hak tersebut.
        */
        'roles' => ['super_admin', 'admin_konten'],

        /*
        | SVG sengaja tidak diikutkan di jalur inline (bisa memuat <script>).
        | Unggahan SVG lewat dashboard dibersihkan oleh App\Cms\Media\SvgSanitizer.
        */
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],

        /* Batas ukuran unggahan dalam kilobyte (10 MB). */
        'max_kb' => 10240,

        /*
        | Berkas yang tertimpa tidak dihapus otomatis saat diganti, supaya
        | penggantian keliru bisa dipulihkan dari log audit. Scheduler
        | `stemc:prune-replaced-media` membersihkan yang lebih tua dari
        | prune_replaced_after_days dan tidak lagi dipakai target mana pun.
        */
        /* Usia minimum (hari) sebelum berkas pengganti boleh dihapus. */
        'prune_replaced_after_days' => (int) env('CMS_PRUNE_REPLACED_DAYS', 90),
    ],

    /*
    | Cadangan ada di disk local (storage/app/private/backups), bukan public.
    | Restore hanya super admin; .env tidak ikut zip.
    */
    'backup' => [
        'directory' => 'backups',
        'keep' => 12,
    ],

    /*
    | Sumber kurasi konten lama. Hanya daftar judul+URL; tidak mengimpor.
    */
    'legacy_source' => [
        'base_url' => env('STEMC_LEGACY_URL', 'https://stem.lppm.usk.ac.id'),
        'pages' => 6,
        'cache_minutes' => 180,
    ],

];
