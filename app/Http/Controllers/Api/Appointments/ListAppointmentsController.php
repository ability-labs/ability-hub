<?php

namespace App\Http\Controllers\Api\Appointments;

use App\Http\Controllers\Controller;
use App\Http\Requests\FetchAppointmentsRequest;
use App\Models\Discipline;
use Illuminate\Http\JsonResponse;

class ListAppointmentsController extends Controller
{
    public function __invoke(FetchAppointmentsRequest $request): JsonResponse
    {
        $user = $request->user();

        $start = $request->startDate();
        $end = $request->endDate();

        $appointments = $user
            ->appointments()
            ->with(['learner', 'operator', 'discipline'])
            ->whereBetween('starts_at', [$start, $end])
            ->when($request->validated('operator_id'), function ($query, $operatorId) {
                return $query->where('operator_id', $operatorId);
            })
            ->when($request->validated('learner_id'), function ($query, $learnerId) {
                return $query->where('learner_id', $learnerId);
            })
            ->orderBy('starts_at')
            ->get()
            ->map(fn ($appointment) => $appointment->toFullCalendar())
            ->values();

        $operators = $user->operators()->with('disciplines')->get();
        $learners = $user->learners()->get();
        $disciplines = Discipline::all();
        $appointmentTypes = \App\Models\AppointmentType::all();

        return response()->json([
            'appointments' => $appointments,
            'operators' => $operators,
            'learners' => $learners,
            'disciplines' => $disciplines,
            'appointment_types' => $appointmentTypes,
            'range' => [
                'starts_at' => $start->toDateString(),
                'ends_at' => $end->toDateString(),
            ],
        ]);
    }
}
