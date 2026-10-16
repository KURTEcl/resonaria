<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_case_id')->constrained('cases')->cascadeOnDelete();
            $table->foreignId('to_case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('relation', 32)->default('relacionado');
            $table->string('note', 240)->nullable();
            $table->string('origin', 16)->default('markup');
            $table->timestamps();

            $table->unique(['from_case_id', 'to_case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_links');
    }
};
