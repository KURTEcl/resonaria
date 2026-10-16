<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('follower_key', 64);
            $table->timestamps();

            $table->unique(['case_id', 'follower_key']);
            $table->index('case_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_follows');
    }
};
