<?php

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class UpsertAppointment
{
    public function execute(User $user, array $data, ?Appointment $appointment = null): Appointment
    {
        Log::info("UpsertAppointment execution started");
        return DB::transaction(function () use ($user, $data, $appointment) {
            $isNew = !$appointment;
            
            if ($isNew) {
                $appointment = new Appointment();
                $appointment->user_id = $user->id;
            }

            // Default to Terapia if no type is provided
            if (empty($data['appointment_type_id'])) {
                $therapyType = AppointmentType::where('name->it', 'Terapia')->first();
                $data['appointment_type_id'] = $therapyType?->id;
            }

            $appointment->starts_at = $data['starts_at'];
            $appointment->ends_at = $data['ends_at'];
            $appointment->duration_minutes = $appointment->starts_at->diffInMinutes($appointment->ends_at);
            
            $appointment->discipline_id = $data['discipline_id'] ?? $appointment->discipline_id;
            $appointment->appointment_type_id = $data['appointment_type_id'];
            $appointment->comments = $data['comments'] ?? '';
            
            // For BC and internal logic that might still rely on single ID
            $operatorIds = $data['operator_ids'] ?? [$data['operator_id'] ?? null];
            $learnerIds = $data['learner_ids'] ?? [$data['learner_id'] ?? null];
            
            $operatorIds = array_filter($operatorIds);
            $learnerIds = array_filter($learnerIds);

            if (!empty($operatorIds)) {
                $appointment->operator_id = $operatorIds[0];
            }
            if (!empty($learnerIds)) {
                $appointment->learner_id = $learnerIds[0];
            }

            $appointment->title = 'temp'; // Temporary title
            $appointment->save();

            if (!empty($learnerIds)) {
                $appointment->learners()->sync($learnerIds);
            }
            if (!empty($operatorIds)) {
                $appointment->operators()->sync($operatorIds);
            }

            $appointment->load(['learners', 'operators']);
            
            $learnerNames = $appointment->learners->pluck('full_name')->filter()->join(', ');
            $operatorNames = $appointment->operators->pluck('name')->filter()->join(', ');
            
            $appointment->update([
                'title' => $learnerNames . " (" . $operatorNames . ")"
            ]);

            Log::info("UpsertAppointment saved successfully: ID " . $appointment->id);

            return $appointment->refresh();
        });
    }
}
