<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE product_variants MODIFY stock_status ENUM('in_stock', 'low', 'out_of_stock', 'on_backorder') DEFAULT 'in_stock'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE product_variants MODIFY stock_status ENUM('in_stock', 'out_of_stock', 'on_backorder') DEFAULT 'in_stock'");
    }
};
