<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Update properties table to use new address schema with districts instead of neighborhoods
     * and make street/door_apartment_no nullable as they are optional in the new schema
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Rename neighborhood to district to match new schema
            $table->renameColumn('neighborhood', 'district');

            // Make street and door_apartment_no nullable since they're optional in the new schema
            $table->string('street')->nullable()->change();
            $table->string('door_apartment_no')->nullable()->change();
        });

        // Update indexes to use 'district' instead of 'neighborhood'
        Schema::table('properties', function (Blueprint $table) {
            // Drop old index with 'neighborhood'
            $table->dropIndex(['company_id', 'city', 'neighborhood']);

            // Add new index with 'district'
            $table->index(['company_id', 'city', 'district']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Drop new index with 'district'
            $table->dropIndex(['company_id', 'city', 'district']);

            // Add back old index with 'neighborhood'
            $table->index(['company_id', 'city', 'neighborhood']);
        });

        Schema::table('properties', function (Blueprint $table) {
            // Rename district back to neighborhood
            $table->renameColumn('district', 'neighborhood');

            // Make street and door_apartment_no required again
            $table->string('street')->nullable(false)->change();
            $table->string('door_apartment_no')->nullable(false)->change();
        });
    }
};
