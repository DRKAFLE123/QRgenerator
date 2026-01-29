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
        Schema::table('bio_page_analytics', function (Blueprint $table) {
            $table->string('device_type')->nullable()->after('user_agent'); // mobile, desktop, tablet, robot
            $table->string('browser')->nullable()->after('device_type');
            $table->string('os')->nullable()->after('browser');
            $table->string('city')->nullable()->after('os');
            $table->string('country')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bio_page_analytics', function (Blueprint $table) {
            $table->dropColumn(['device_type', 'browser', 'os', 'city', 'country']);
        });
    }
};
