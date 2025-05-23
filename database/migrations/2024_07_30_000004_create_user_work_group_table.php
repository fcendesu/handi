<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_work_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('work_group_id')->constrained('work_groups')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'work_group_id']); // Prevent duplicate assignments
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_work_group');
    }
};
