<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->unsignedBigInteger('anilist_id')->nullable()->unique()->after('slug');
        });

        Schema::table('people', function (Blueprint $table) {
            $table->unsignedBigInteger('anilist_id')->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropUnique(['anilist_id']);
            $table->dropColumn('anilist_id');
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropUnique(['anilist_id']);
            $table->dropColumn('anilist_id');
        });
    }
};
