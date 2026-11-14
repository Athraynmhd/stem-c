<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul data riset dari dokumen kebutuhan: peneliti, katalog proyek,
 * produk/HKI, dan MoU/MoA — entitas terstruktur, bukan "post" generik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('researchers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('degree')->nullable();
            $table->string('photo')->nullable();
            $table->string('expertise')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('research_group')->nullable();
            $table->text('education')->nullable();
            $table->text('interests')->nullable();
            $table->text('bio')->nullable();
            $table->string('email')->nullable();
            $table->string('google_scholar')->nullable();
            $table->string('scopus')->nullable();
            $table->string('sinta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('research_projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('year');
            $table->string('leader')->nullable();
            $table->string('members')->nullable();
            $table->string('funding')->nullable();
            $table->string('status')->default('berjalan'); // berjalan|selesai
            $table->text('abstract')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['year', 'status']);
        });

        Schema::create('innovations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            // produk|paten|hak_cipta|merek — satu tabel untuk §4.1 poin 7–8
            $table->string('type')->default('produk');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('registration_number')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('holders')->nullable();
            $table->string('hki_status')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
        });

        Schema::create('partnerships', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('partner_name');
            $table->string('type')->default('mou'); // mou|moa
            $table->text('scope')->nullable();
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnerships');
        Schema::dropIfExists('innovations');
        Schema::dropIfExists('research_projects');
        Schema::dropIfExists('researchers');
    }
};
