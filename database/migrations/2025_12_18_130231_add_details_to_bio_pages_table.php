<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bio_pages', function (Blueprint $table) {
            $table->string('theme')->default('modern')->after('bg_color');
            $table->string('logo_path')->nullable()->after('theme');
            $table->string('cover_path')->nullable()->after('logo_path');
            $table->string('website')->nullable()->after('cover_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bio_pages', function (Blueprint $table) {
            $table->dropColumn(['theme', 'logo_path', 'cover_path', 'website']);
        });
    }
};
