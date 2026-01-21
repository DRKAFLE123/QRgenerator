<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bio_pages', function (Blueprint $table) {
            $table->string('status')->default('active')->index()->after('website'); // active, inactive
            $table->string('payment_status')->default('unpaid')->index()->after('status'); // unpaid, paid
            $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_status');
            $table->timestamp('payment_date')->nullable()->after('payment_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bio_pages', function (Blueprint $table) {
            $table->dropColumn(['status', 'payment_status', 'payment_amount', 'payment_date']);
        });
    }
};
