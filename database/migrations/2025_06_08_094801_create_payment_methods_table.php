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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade'); // For company payment methods
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // For solo handyman payment methods
            $table->string('name'); // Payment method name (e.g., "Nakit", "Kredi Kartı", "Banka Transferi")
            $table->text('description')->nullable(); // Optional description
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id']);
            $table->index(['user_id']);

            // Ensure name uniqueness within the same owner (company or user)
            $table->unique(['company_id', 'name'], 'unique_company_payment_method');
            $table->unique(['user_id', 'name'], 'unique_user_payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
