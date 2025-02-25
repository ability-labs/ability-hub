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
        Schema::create('datasheets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');

            $table->foreignUuid('learner_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUuid('operator_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->schemalessAttributes('data')->nullable();

            $table->dateTime('finalized_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datasheets');
    }
};
