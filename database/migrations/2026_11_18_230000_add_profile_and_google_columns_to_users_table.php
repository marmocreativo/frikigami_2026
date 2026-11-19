<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name');
            $table->string('avatar')->nullable()->after('last_name');          // foto subida (disco público)
            $table->string('google_id')->nullable()->unique()->after('avatar');
            $table->string('google_avatar', 500)->nullable()->after('google_id'); // foto de Google (URL)
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropColumn(['last_name', 'avatar', 'google_id', 'google_avatar']);
        });
    }
};
