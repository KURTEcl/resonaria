<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->char('claim_key', 64);
            $table->text('claim');
            $table->string('verdict', 32);
            $table->text('response');
            $table->string('heard_on', 160)->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->string('source_title')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['case_id', 'claim_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_claims');
    }
};
