<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('lead');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->text('hook')->nullable()->after('title');
            $table->text('what_changes')->nullable()->after('summary');
            $table->text('who_affected')->nullable()->after('what_changes');
            $table->text('detail')->nullable()->after('after_text');
        });

        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('platform', 32);
            $table->string('url', 2048);
            $table->text('caption')->nullable();
            $table->string('author_handle')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');

        Schema::table('cases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['hook', 'what_changes', 'who_affected', 'detail']);
        });

        Schema::dropIfExists('categories');
    }
};
