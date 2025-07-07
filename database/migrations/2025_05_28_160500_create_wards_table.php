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
        Schema::create('wards', function (Blueprint $table) {
            $table->string('code', 20)->primary();
            $table->string('name');
            $table->string('slug');
            $table->string('type', 50);
            $table->string('name_with_type');
            $table->string('path')->nullable();
            $table->string('path_with_type')->nullable();
            $table->string('district_code')->nullable(); // Nullable vì data hiện tại không có district
            $table->string('province_code', 20); // Mapping với provinces.code
            $table->timestamps();

            $table->foreign('province_code')->references('code')->on('provinces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wards');
    }
};
