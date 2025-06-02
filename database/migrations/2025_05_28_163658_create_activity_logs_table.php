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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // User who performed action
            $table->string('log_name')->nullable()->index(); // e.g., product_update, order_creation
            $table->text('description');
            $table->nullableMorphs('subject'); // The model that was affected (e.g., Product, Order)
            $table->nullableMorphs('causer'); // The model that caused the action (usually User)
            $table->json('properties')->nullable(); // Additional data (old values, new values)
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
