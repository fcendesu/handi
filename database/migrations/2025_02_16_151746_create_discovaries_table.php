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
        Schema::create('discovaries', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email');
            $table->text('discovery');
            $table->text('todo_list');
            $table->text('note_to_customer')->nullable();
            $table->text('note_to_handi')->nullable();
            $table->string('status')->default('pending');
            $table->integer('completion_time')->nullable();
            $table->date('offer_valid_until')->nullable();
            $table->decimal('service_cost', 10, 2)->default(0);
            $table->decimal('transportation_cost', 10, 2)->default(0);
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('extra_fee', 10, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->text('payment_method')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();
        });

        Schema::create('discovery_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discovery_id')->constrained('discovaries')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discovaries');
        Schema::dropIfExists('discovery_item');
    }
};
