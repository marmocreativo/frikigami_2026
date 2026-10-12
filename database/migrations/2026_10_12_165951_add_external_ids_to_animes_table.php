<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animes', function (Blueprint $table) {
            $table->unsignedBigInteger('anilist_id')->nullable()->unique()->after('slug');
            $table->unsignedBigInteger('mal_id')->nullable()->unique()->after('anilist_id');
        });
    }

    public function down(): void
    {
        Schema::table('animes', function (Blueprint $table) {
            $table->dropUnique(['anilist_id']);
            $table->dropUnique(['mal_id']);
            $table->dropColumn(['anilist_id', 'mal_id']);
        });
    }
};
