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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained()->onDelete('restrict'); // Or set null/cascade based on logic
            // $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null'); // Assuming you have a brands table
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('sku_prefix')->nullable();
            $table->enum('type', ['simple', 'variable'])->default('simple');
            $table->enum('status', ['published', 'draft', 'pending_review', 'trashed'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('tags')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->text('warranty_information')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes(); // For soft delete functionality
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
