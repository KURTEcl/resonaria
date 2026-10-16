<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_analytics_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->date('metric_date');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('source_clicks')->default(0);
            $table->timestamps();

            $table->unique(['case_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_analytics_daily');
    }
};
