<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('panduan'); // regulasi|panduan|formulir|laporan|materi
            $table->text('description')->nullable();
            // Sebagian dokumen diunggah ke server, sebagian lagi sudah terbit di
            // repositori lain (Drive, OJS, situs universitas) sehingga cukup
            // ditautkan. Salah satu dari keduanya wajib diisi.
            $table->string('file')->nullable();
            $table->string('external_url')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->date('published_on')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['category', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
