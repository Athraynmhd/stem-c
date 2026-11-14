<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;

/**
 * Login panel tanpa pilihan "Ingat saya".
 *
 * Kotak bawaan Filament memasang cookie `remember_web_*` berumur 576.000 menit
 * (400 hari) yang menghidupkan kembali sesi jauh setelah SESSION_LIFETIME habis.
 * Untuk akun yang bisa mengganti gambar situs langsung dari halaman publik,
 * laptop yang dipakai bergantian jadi risiko: siapa pun yang membuka peramban
 * itu langsung berstatus pengelola.
 *
 * Menghapus komponennya sudah cukup — `authenticate()` membaca
 * `$data['remember'] ?? false`, jadi tanpa field itu nilainya selalu false.
 */
class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
            ]);
    }
}
