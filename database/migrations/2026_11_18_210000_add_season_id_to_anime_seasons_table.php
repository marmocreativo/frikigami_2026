<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estreno (temporada del año) de cada temporada de una serie:
     * Frieren estrenó en Otoño 2023, pero su temporada 2 en Invierno 2026.
     */
    public function up(): void
    {
        Schema::table('anime_seasons', function (Blueprint $table) {
            $table->foreignId('season_id')->nullable()->after('year')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('anime_seasons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('season_id');
        });
    }
};
