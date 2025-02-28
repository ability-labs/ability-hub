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
        Schema::table('datasheet_types', function (Blueprint $table) {
            $table->json('description')->nullable();
            $table->json('instruction')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('datasheet_types', function (Blueprint $table) {
            $table->dropColumn('description')->nullable();
            $table->dropColumn('instruction')->nullable();
        });
    }
};
