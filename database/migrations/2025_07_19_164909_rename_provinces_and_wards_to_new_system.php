<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('provinces', 'provinces_new');
        Schema::rename('wards', 'wards_new');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('provinces_new', 'provinces');
        Schema::rename('wards_new', 'wards');
    }
};
