<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bio_pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('links'); // Stored as JSON
            $table->string('color')->default('#FF6B6B');
            $table->string('bg_color')->default('#F8F9FA');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bio_pages');
    }
};
