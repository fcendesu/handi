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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade'); // Made nullable for solo handymen
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Added for solo handyman ownership
            $table->string('name'); // User-given name for this property/address

            // Hierarchical address structure
            $table->string('city'); // Lefkoşa, Girne, Mağusa, İskele, Güzelyurt, Lefke
            $table->string('district'); // Changed from neighborhood to district
            $table->string('site_name')->nullable(); // Optional
            $table->string('building_name')->nullable(); // Optional
            $table->string('street')->nullable(); // Made nullable as optional in new schema
            $table->string('door_apartment_no')->nullable(); // Made nullable as optional in new schema

            // Map location (optional)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Additional fields
            $table->text('notes')->nullable(); // Any additional notes about the property
            $table->boolean('is_active')->default(true); // To soft delete without actual deletion

            $table->timestamps();

            // Updated indexes to use 'district' instead of 'neighborhood'
            $table->index(['company_id', 'city', 'district']);
            $table->index(['company_id', 'is_active']);
            $table->index(['user_id', 'is_active']); // Added index for solo handymen
        });

        // Add foreign key constraint to discoveries table for property_id
        Schema::table('discoveries', function (Blueprint $table) {
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discoveries', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
        });
        
        Schema::dropIfExists('properties');
    }
};
