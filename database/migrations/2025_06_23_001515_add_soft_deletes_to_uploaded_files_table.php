<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('uploaded_files', function (Blueprint $table) {
         $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
        $table->softDeletes(); 
       
    });
}

public function down(): void
{
    Schema::table('uploaded_files', function (Blueprint $table) {
        $table->dropSoftDeletes(); 
    });
}
};
