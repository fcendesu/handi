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
            $table->tinyInteger('priority')->default(1)->after('status')->comment('Priority: 1=least urgent, 2=medium, 3=highest/urgent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discoveries', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
