<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('authors');
            $table->string('type')->default('jurnal'); // jurnal|prosiding|buku|hki|paten
            $table->string('venue')->nullable(); // nama jurnal, konferensi, atau penerbit
            $table->unsignedSmallInteger('year');
            $table->string('doi')->nullable();
            $table->string('url')->nullable();
            $table->text('abstract')->nullable();
            $table->string('file')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['type', 'year']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->nullable(); // seminar|workshop|pelatihan|kunjungan
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('registration_url')->nullable();
            $table->string('status')->default('draft'); // draft|published
            $table->timestamps();

            $table->index('starts_at');
        });

        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('photo'); // photo|video
            $table->string('album')->nullable();
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->date('captured_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['album', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_items');
        Schema::dropIfExists('events');
        Schema::dropIfExists('publications');
    }
};
