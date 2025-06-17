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
        Schema::create('discoveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('work_group_id')->nullable()->constrained('work_groups')->onDelete('set null');
            $table->foreignId('property_id')->nullable(); // Will add constraint after properties table is created
            $table->string('share_token', 64)->nullable()->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('discovery');
            $table->text('todo_list');
            $table->text('note_to_customer')->nullable();
            $table->text('note_to_handi')->nullable();
            $table->string('status')->default('pending');
            $table->tinyInteger('priority')->default(1)->comment('Priority: 1=least urgent, 2=medium, 3=highest/urgent'); // Added priority field
            $table->integer('completion_time')->nullable();
            $table->date('offer_valid_until')->nullable();
            $table->decimal('service_cost', 10, 2)->default(0);
            $table->decimal('transportation_cost', 10, 2)->default(0);
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('extra_fee', 10, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->onDelete('set null');
            $table->json('images')->nullable();
            $table->timestamps();
        });

        Schema::create('discovery_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discovery_id')->constrained('discoveries')->onDelete('cascade');
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
        Schema::table('discoveries', function (Blueprint $table) {
            // Drop foreign keys in the correct order if they might exist
            if (Schema::hasColumn('discoveries', 'creator_id')) {
                $table->dropForeign(['creator_id']);
            }
            if (Schema::hasColumn('discoveries', 'assignee_id')) {
                $table->dropForeign(['assignee_id']);
            }
            if (Schema::hasColumn('discoveries', 'company_id')) {
                $table->dropForeign(['company_id']);
            }
            if (Schema::hasColumn('discoveries', 'work_group_id')) {
                $table->dropForeign(['work_group_id']);
            }
        });
        Schema::dropIfExists('discovery_item');
        Schema::dropIfExists('discoveries');
    }
};
