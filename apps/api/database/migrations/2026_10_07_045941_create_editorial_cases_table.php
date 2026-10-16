<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->json('areas')->nullable();
            $table->json('change_types')->nullable();
            $table->string('procedural_status', 64);
            $table->string('public_status_label', 64)->nullable();
            $table->string('evidence_status', 64)->default('unknown');
            $table->string('observed_effect', 64)->default('unknown');
            $table->string('publication_status', 32)->default('draft')->index();
            $table->unsignedInteger('content_version')->default(1);
            $table->text('before_text')->nullable();
            $table->text('after_text')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image_url')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->timestamps();
        });

        Schema::create('case_slug_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('old_slug')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_slug_histories');
        Schema::dropIfExists('cases');
    }
};
