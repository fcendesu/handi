<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('discoverys', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_number');
            $table->string('customer_email');
            $table->text('discovery');
            $table->text('todolist')->nullable();
            $table->json('images')->nullable(); // Store multiple image paths
            $table->boolean('priority')->default(false); // Change from integer to boolean
            $table->text('note_to_customer')->nullable();
            $table->text('note_to_handi')->nullable();
            $table->enum('status', ['awaiting_approval', 'pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->integer('completion_time')->nullable(); // Changed to integer for days
            $table->timestamp('offer_valid_until')->nullable();

            // Add cost-related columns
            $table->decimal('service_cost', 10, 2)->default(0);
            $table->decimal('transportation_cost', 10, 2)->default(0);
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('extra_fee', 10, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0); // Percentage
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable();  // Changed from enum to string
            $table->json('payment_details')->nullable(); // For storing multiple payment methods if needed

            $table->timestamps();
        });

        // Create pivot table with custom price
        Schema::create('discovery_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discovery_id')->constrained('discoverys')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('custom_price', 10, 2); // Add custom price column
            $table->integer('quantity')->default(1);  // Add quantity field
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discovery_item');
        Schema::dropIfExists('discoverys');
    }
};
