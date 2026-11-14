<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_edit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Nama disalin, bukan hanya direlasikan: log audit harus tetap
            // terbaca setelah akun pelakunya dihapus.
            $table->string('user_name');
            $table->string('target', 120);
            $table->string('old_path')->nullable();
            $table->string('new_path');
            $table->string('action', 20)->default('replace');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['target', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_edit_logs');
    }
};
