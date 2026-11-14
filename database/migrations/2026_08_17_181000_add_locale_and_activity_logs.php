<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom terjemahan EN + jejak audit CRUD dashboard.
 * Dwibahasa disengaja minimal (profil, berita, publikasi) sesuai PDF Fase 4.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->longText('content_en')->nullable()->after('content');
            $table->string('section_en')->nullable()->after('section');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->text('excerpt_en')->nullable()->after('excerpt');
            $table->longText('body_en')->nullable()->after('body');
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->text('abstract_en')->nullable()->after('abstract');
        });

        Schema::create('content_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name');
            $table->string('action', 20); // created|updated|deleted|restored
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_activity_logs');

        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'abstract_en']);
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'excerpt_en', 'body_en']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'content_en', 'section_en']);
        });
    }
};
