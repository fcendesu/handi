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
        Schema::table('transaction_logs', function (Blueprint $table) {
            // Add indexes for better filtering performance
            $table->index(['entity_type', 'created_at'], 'idx_entity_type_created');
            $table->index(['action', 'created_at'], 'idx_action_created');
            $table->index(['performed_by_type', 'created_at'], 'idx_performer_created');
            $table->index(['user_id', 'entity_type', 'created_at'], 'idx_user_entity_created');

            // Index for cleanup operations (delete old logs)
            $table->index('created_at', 'idx_created_at_cleanup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_logs', function (Blueprint $table) {
            $table->dropIndex('idx_entity_type_created');
            $table->dropIndex('idx_action_created');
            $table->dropIndex('idx_performer_created');
            $table->dropIndex('idx_user_entity_created');
            $table->dropIndex('idx_created_at_cleanup');
        });
    }
};
