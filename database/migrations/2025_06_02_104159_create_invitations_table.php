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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Unique invitation code
            $table->string('email'); // Email address being invited
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade'); // Admin who sent invitation
            $table->json('work_group_ids')->nullable(); // Pre-assigned work groups
            $table->timestamp('expires_at'); // Expiration date
            $table->timestamp('used_at')->nullable(); // When invitation was used
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // User who used the invitation
            $table->enum('status', ['pending', 'used', 'expired'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
