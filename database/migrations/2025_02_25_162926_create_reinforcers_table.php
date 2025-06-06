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
        Schema::create('reinforcers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key');
            $table->json('category');
            $table->json('subcategory');
            $table->json('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reinforcers');
    }
};
