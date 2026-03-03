<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $appointments = DB::table('appointments')->get();

        foreach ($appointments as $appointment) {
            DB::table('appointment_learner')->insert([
                'appointment_id' => $appointment->id,
                'learner_id' => $appointment->learner_id,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ]);

            DB::table('appointment_operator')->insert([
                'appointment_id' => $appointment->id,
                'operator_id' => $appointment->operator_id,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('appointment_learner')->truncate();
        DB::table('appointment_operator')->truncate();
    }
};
