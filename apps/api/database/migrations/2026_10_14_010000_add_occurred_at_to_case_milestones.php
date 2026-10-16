<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_milestones', function (Blueprint $table) {
            $table->timestamp('occurred_at')->nullable()->after('occurred_on');
        });
    }

    public function down(): void
    {
        Schema::table('case_milestones', function (Blueprint $table) {
            $table->dropColumn('occurred_at');
        });
    }
};
