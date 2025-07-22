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
        Schema::create('pos_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('register_id')->constrained('registers');
    $table->foreignId('user_id')->constrained('users');
    $table->decimal('opening_balance', 15, 2)->default(0);
    $table->decimal('closing_balance', 15, 2)->nullable();
    $table->decimal('calculated_balance', 15, 2)->nullable();
    $table->string('status')->default('open'); // open, closed
    $table->timestamp('opened_at')->useCurrent();
    $table->timestamp('closed_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
