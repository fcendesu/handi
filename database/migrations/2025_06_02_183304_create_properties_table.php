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
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name'); // User-given name for this property/address

            // Hierarchical address structure
            $table->string('city'); // Lefkoşa, Girne, Mağusa, İskele, Güzelyurt, Lefke
            $table->string('neighborhood');
            $table->string('site_name')->nullable(); // Optional
            $table->string('building_name')->nullable(); // Optional
            $table->string('street'); // Required
            $table->string('door_apartment_no'); // Required

            // Map location (optional)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Additional fields
            $table->text('notes')->nullable(); // Any additional notes about the property
            $table->boolean('is_active')->default(true); // To soft delete without actual deletion

            $table->timestamps();

            // Index for better performance
            $table->index(['company_id', 'city', 'neighborhood']);
            $table->index(['company_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
