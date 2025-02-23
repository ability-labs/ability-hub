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
        Schema::create('operators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('vat_id')->nullable();
            $table->timestamps();
        });

        Schema::create('discipline_operator', function (Blueprint $table) {
            $table->foreignUuid('operator_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUuid('discipline_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operators');
    }
};
