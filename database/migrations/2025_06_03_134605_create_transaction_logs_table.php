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
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('discovery_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('action'); // created, status_changed, approved, rejected, assigned, updated, deleted
            $table->string('entity_type')->default('discovery'); // for future extensibility
            $table->foreignId('entity_id')->nullable(); // generic entity ID for future use
            $table->json('old_values')->nullable(); // previous state
            $table->json('new_values')->nullable(); // new state
            $table->json('metadata')->nullable(); // additional context
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('performed_by_type')->default('user'); // user, customer, system
            $table->string('performed_by_identifier')->nullable(); // email for customer actions
            $table->timestamps();

            $table->index(['discovery_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_logs');
    }
};
