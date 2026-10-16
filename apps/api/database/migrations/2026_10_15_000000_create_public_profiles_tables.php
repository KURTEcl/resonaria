<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 128)->unique();
            $table->string('display_name', 180);
            $table->string('short_name', 80)->nullable();
            $table->string('kind', 32)->default('ejecutivo');
            $table->string('status', 16)->default('active');
            $table->string('photo_url', 2048)->nullable();
            $table->timestamp('photo_updated_at')->nullable();
            $table->text('bio')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('profile_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('public_profiles')->cascadeOnDelete();
            $table->string('alias', 240);
            $table->string('alias_type', 32)->default('nombre_completo');
            $table->timestamps();

            $table->unique(['profile_id', 'alias']);
            $table->index('alias');
        });

        Schema::create('profile_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('public_profiles')->cascadeOnDelete();
            $table->string('title', 180);
            $table->string('organization', 180)->nullable();
            $table->string('level', 32)->default('otro');
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->timestamps();

            $table->index(['profile_id', 'ended_on']);
        });

        Schema::create('profile_social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('public_profiles')->cascadeOnDelete();
            $table->foreignId('profile_role_id')->nullable()->constrained('profile_roles')->nullOnDelete();
            $table->string('platform', 16);
            $table->string('handle', 80);
            $table->string('account_kind', 24)->default('personal');
            $table->string('label', 240)->nullable();
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->string('status', 16)->default('active');
            $table->boolean('use_for_avatar')->default(false);
            $table->string('avatar_remote_url', 2048)->nullable();
            $table->timestamp('avatar_fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['platform', 'handle', 'started_on']);
            $table->index(['platform', 'handle']);
        });

        Schema::table('public_profiles', function (Blueprint $table) {
            $table->foreignId('photo_source_account_id')
                ->nullable()
                ->after('photo_url')
                ->constrained('profile_social_accounts')
                ->nullOnDelete();
        });

        Schema::create('profile_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('public_profiles')->cascadeOnDelete();
            $table->string('mentionable_type');
            $table->unsignedBigInteger('mentionable_id');
            $table->string('role', 32)->default('mencionado');
            $table->string('origin', 16)->default('manual');
            $table->string('confidence', 16)->default('confirmed');
            $table->string('excerpt', 500)->nullable();
            $table->string('context_note', 240)->nullable();
            $table->timestamps();

            $table->unique(['profile_id', 'mentionable_type', 'mentionable_id'], 'profile_mentions_unique');
            $table->index(['mentionable_type', 'mentionable_id']);
            $table->index(['confidence', 'origin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_mentions');
        Schema::table('public_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('photo_source_account_id');
        });
        Schema::dropIfExists('profile_social_accounts');
        Schema::dropIfExists('profile_roles');
        Schema::dropIfExists('profile_aliases');
        Schema::dropIfExists('public_profiles');
    }
};
