<?php

namespace App\Http\Controllers\Cms;

use App\Cms\Media\EditableImage;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InlineImageController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $maksMb = round(EditableImage::maxKilobytes() / 1024, 1);
        $format = strtoupper(implode(', ', (array) config('cms.inline_edit.mimes', [])));

        $data = $request->validate([
            'target' => ['required', 'string', 'max:120'],
            'image' => EditableImage::uploadRules(),
        ], [
            'image.max' => "Ukuran gambar maksimal {$maksMb} MB.",
            'image.mimes' => "Format gambar harus {$format}. Berkas SVG hanya boleh diganti dari dashboard.",
            // Aturan `uploaded` gagal ketika PHP sendiri membuang berkasnya —
            // hampir selalu karena melewati upload_max_filesize / post_max_size.
            // Pesan bawaannya ("The image failed to upload.") tidak menyebut
            // sebabnya sama sekali.
            'image.uploaded' => 'Gambar ditolak server sebelum sampai ke aplikasi. Batas unggah PHP saat ini '
                .ini_get('upload_max_filesize').' per berkas (post_max_size '.ini_get('post_max_size')
                .'). Perkecil gambarnya atau naikkan batas itu di php.ini.',
        ]);

        $pengguna = $request->user();

        // Gerbang ini menutup sekaligus tiga hal: sakelar produksi yang mati,
        // role di luar daftar, dan target di luar hak akses pengguna.
        abort_unless($pengguna instanceof User && EditableImage::can($pengguna, $data['target']), 403);

        return response()->json([
            'url' => EditableImage::replace($data['target'], $request->file('image'), $pengguna, $request),
        ]);
    }
}
