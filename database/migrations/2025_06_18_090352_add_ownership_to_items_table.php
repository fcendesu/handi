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
        Schema::table('items', function (Blueprint $table) {
            // Add ownership columns
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->after('id');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade')->after('user_id');
            
            // Add indexes for performance
            $table->index(['user_id']);
            $table->index(['company_id']);
            
            // Ensure that item has either user_id or company_id, but not both
            $table->index(['user_id', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['company_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['company_id']);
            $table->dropIndex(['user_id', 'company_id']);
            $table->dropColumn(['user_id', 'company_id']);
        });
    }
};
