<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_milestones', function (Blueprint $table) {
            $table->text('note')->nullable()->after('label');
            $table->string('direction', 32)->default('incierto')->after('note');
            $table->string('source_url', 2048)->nullable()->after('direction');
            $table->string('source_title')->nullable()->after('source_url');
        });
    }

    public function down(): void
    {
        Schema::table('case_milestones', function (Blueprint $table) {
            $table->dropColumn(['note', 'direction', 'source_url', 'source_title']);
        });
    }
};
