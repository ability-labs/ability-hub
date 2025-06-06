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
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUuid('learner_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUuid('operator_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUuid('discipline_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->longText('comments')->nullable();

            $table->datetime('starts_at');
            $table->datetime('ends_at');

            $table->datetime('learner_signed_at')->nullable();
            $table->datetime('operator_signed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
