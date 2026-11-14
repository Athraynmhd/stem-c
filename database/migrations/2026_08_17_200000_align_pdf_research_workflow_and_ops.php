<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->string('code', 40)->nullable();
            $table->text('focus')->nullable();
            $table->text('focus_en')->nullable();
            $table->string('image')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('folder')->default('library');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime', 80)->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->string('checksum', 64)->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->string('alt')->nullable();
            $table->timestamps();
            $table->index(['folder', 'created_at']);
        });

        Schema::create('login_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('email')->nullable();
            $table->string('event', 20);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['event', 'created_at']);
        });

        Schema::create('publication_researcher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $table->foreignId('researcher_id')->constrained()->cascadeOnDelete();
            $table->unique(['publication_id', 'researcher_id']);
        });

        Schema::create('research_project_researcher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('researcher_id')->constrained()->cascadeOnDelete();
            $table->string('role', 40)->nullable();
            $table->unique(['research_project_id', 'researcher_id'], 'rpr_project_researcher_unique');
        });

        Schema::table('researchers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('research_group_id')->nullable()->after('user_id')->constrained('research_groups')->nullOnDelete();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('name_en')->nullable();
            $table->text('bio_en')->nullable();
            $table->string('expertise_en')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('workflow_status', 20)->default('published')->after('is_published');
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->index('workflow_status');
        });

        Schema::table('research_projects', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('research_group_id')->nullable()->constrained('research_groups')->nullOnDelete();
            $table->string('workflow_status', 20)->default('published')->after('is_published');
            $table->string('title_en')->nullable();
            $table->text('abstract_en')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->boolean('is_featured')->default(false);
        });

        Schema::table('innovations', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('workflow_status', 20)->default('published')->after('is_published');
            $table->string('title_en')->nullable();
            $table->text('description_en')->nullable();
            $table->text('specifications')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
        });

        Schema::table('news', function (Blueprint $table) {
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('tags')->nullable();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('title_en')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->longText('body_en')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->boolean('is_archived')->default(false);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->string('title_en')->nullable();
            $table->text('description_en')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
        });

        Schema::table('partnerships', function (Blueprint $table) {
            $table->string('title_en')->nullable();
            $table->text('scope_en')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('intent', 40)->nullable()->after('subject');
            $table->boolean('consent')->default(false)->after('message');
        });

        DB::table('publications')->where('is_published', true)->update(['workflow_status' => 'published']);
        DB::table('publications')->where('is_published', false)->update(['workflow_status' => 'draft']);
        DB::table('research_projects')->where('is_published', true)->update(['workflow_status' => 'published']);
        DB::table('research_projects')->where('is_published', false)->update(['workflow_status' => 'draft']);
        DB::table('innovations')->where('is_published', true)->update(['workflow_status' => 'published']);
        DB::table('innovations')->where('is_published', false)->update(['workflow_status' => 'draft']);
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn(['intent', 'consent']);
        });

        Schema::table('partnerships', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'scope_en', 'seo_title', 'seo_description']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'description_en', 'seo_title', 'seo_description']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'excerpt_en', 'body_en', 'seo_title', 'seo_description', 'is_archived']);
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'tags']);
        });

        Schema::table('innovations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['workflow_status', 'title_en', 'description_en', 'specifications', 'seo_title', 'seo_description']);
        });

        Schema::table('research_projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('research_group_id');
            $table->dropColumn(['workflow_status', 'title_en', 'abstract_en', 'seo_title', 'seo_description', 'is_featured']);
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['workflow_status', 'seo_title', 'seo_description']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['app_authentication_secret', 'app_authentication_recovery_codes']);
        });

        Schema::table('researchers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('research_group_id');
            $table->dropColumn(['seo_title', 'seo_description', 'name_en', 'bio_en', 'expertise_en']);
        });

        Schema::dropIfExists('research_project_researcher');
        Schema::dropIfExists('publication_researcher');
        Schema::dropIfExists('login_activity_logs');
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('research_groups');
    }
};
