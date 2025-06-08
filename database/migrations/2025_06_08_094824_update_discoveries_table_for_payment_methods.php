<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('discoveries', function (Blueprint $table) {
            // Add the new payment_method_id column
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->onDelete('set null');
            
            // Keep the old payment_method column for backward compatibility during transition
            // We'll remove it in a future migration after data migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discoveries', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');
        });
    }
};
