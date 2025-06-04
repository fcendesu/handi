<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Add user_id for solo handyman ownership
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->after('company_id');

            // Make company_id nullable since solo handymen won't have a company
            $table->foreignId('company_id')->nullable()->change();

            // Add index for better performance
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'is_active']);
            $table->dropColumn('user_id');

            // Restore company_id as non-nullable
            $table->foreignId('company_id')->nullable(false)->change();
        });
    }
};
