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
        $therapyType = \App\Models\AppointmentType::where('name->it', 'Terapia')->first();

        if ($therapyType) {
            \Illuminate\Support\Facades\DB::table('appointments')
                ->whereNull('appointment_type_id')
                ->update(['appointment_type_id' => $therapyType->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
