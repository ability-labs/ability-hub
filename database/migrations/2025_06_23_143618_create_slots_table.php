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
        Schema::create('slots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('discipline_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('week_day')->unsigned();
            $table->string('day_span');
            $table->integer('start_time_hour')->unsigned();
            $table->integer('start_time_minute')->unsigned();
            $table->integer('end_time_hour')->unsigned();
            $table->integer('end_time_minute')->unsigned();
            $table->integer('duration_minutes')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
