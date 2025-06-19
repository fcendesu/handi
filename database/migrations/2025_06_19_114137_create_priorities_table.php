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
        Schema::create('priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#6B7280'); // Default gray color
            $table->integer('level')->default(1); // 1 = lowest priority, higher number = higher priority
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // For solo handymen
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade'); // For companies
            $table->boolean('is_default')->default(false); // Mark default priorities
            $table->timestamps();

            // Ensure either user_id or company_id is set, but not both
            $table->index(['user_id', 'level']);
            $table->index(['company_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priorities');
    }
};
