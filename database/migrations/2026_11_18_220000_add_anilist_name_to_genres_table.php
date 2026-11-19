<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nombre del género en AniList (en inglés), para emparejar al importar.
     */
    public function up(): void
    {
        Schema::table('genres', function (Blueprint $table) {
            $table->string('anilist_name')->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('genres', function (Blueprint $table) {
            $table->dropUnique(['anilist_name']);
            $table->dropColumn('anilist_name');
        });
    }
};
