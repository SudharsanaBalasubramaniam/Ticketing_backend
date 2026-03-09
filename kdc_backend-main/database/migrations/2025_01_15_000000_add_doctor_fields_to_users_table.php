<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('currently_practicing')->nullable()->after('qualification');
            $table->string('experience')->nullable()->after('currently_practicing');
            $table->text('awards_achievements')->nullable()->after('experience');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['currently_practicing', 'experience', 'awards_achievements']);
        });
    }
};
