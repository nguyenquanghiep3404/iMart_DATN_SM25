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
        Schema::create('provinces_old', function (Blueprint $table) {
            $table->string('code', 20)->primary();
            $table->string('name');
            $table->string('slug');
            $table->string('type');
            $table->string('name_with_type');
        });

        Schema::create('districts_old', function (Blueprint $table) {
            $table->string('code', 20)->primary();
            $table->string('name');
            $table->string('type');
            $table->string('name_with_type');
            $table->string('path_with_type');
            $table->string('parent_code');
            $table->foreign('parent_code')->references('code')->on('provinces_old')->onDelete('cascade');
        });

        Schema::create('wards_old', function (Blueprint $table) {
            $table->string('code', 20)->primary();
            $table->string('name');
            $table->string('type');
            $table->string('name_with_type');
            $table->string('path_with_type');
            $table->string('parent_code');
            $table->foreign('parent_code')->references('code')->on('districts_old')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wards_old');
        Schema::dropIfExists('districts_old');
        Schema::dropIfExists('provinces_old');
    }
};
