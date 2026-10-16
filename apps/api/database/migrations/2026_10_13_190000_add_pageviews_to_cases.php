<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->unsignedBigInteger('pageviews')->default(0)->after('sources_count');
        });

        DB::table('cases')->update([
            'pageviews' => DB::raw(
                '(SELECT COALESCE(SUM(views), 0) FROM case_analytics_daily WHERE case_analytics_daily.case_id = cases.id)'
            ),
        ]);
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('pageviews');
        });
    }
};
