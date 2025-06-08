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
        Schema::table('payment_methods', function (Blueprint $table) {
            // First drop indexes that reference the is_active column
            $table->dropIndex(['company_id', 'is_active']);
            $table->dropIndex(['user_id', 'is_active']);
        });
        
        Schema::table('payment_methods', function (Blueprint $table) {
            // Then drop the column
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            
            // Recreate the indexes
            $table->index(['company_id', 'is_active']);
            $table->index(['user_id', 'is_active']);
        });
    }
};
