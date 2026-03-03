<?php

namespace App\Http\Controllers;

use App\Actions\Appointments\ClearWeeklyAppointments;
use App\Actions\Appointments\DuplicateWeeklyAppointments;
use App\Http\Requests\ClearWeeklyAppointmentsRequest;
use App\Http\Requests\DuplicateWeeklyAppointmentsRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('appointments.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();
        
        $operatorIds = $request->input('operator_ids', [$request->input('operator_id')]);
        $learnerIds = $request->input('learner_ids', [$request->input('learner_id')]);

        $data['operator_id'] = $operatorIds[0];
        $data['learner_id'] = $learnerIds[0];
        $data['title'] = 'temp';
        $data['user_id'] = $request->user()->id;

        $appointment = Appointment::create(Arr::except($data, ['operator_ids', 'learner_ids']));
        $appointment->learners()->sync($learnerIds);
        $appointment->operators()->sync($operatorIds);

        $appointment->load(['learners', 'operators', 'discipline']);
        
        $learnerNames = $appointment->learners->pluck('full_name')->join(', ');
        $operatorNames = $appointment->operators->pluck('name')->join(', ');
        $appointment->update(['title' => $learnerNames . " (". $operatorNames .")"]);

        $message = __("The :resource was created!", ['resource' => __('Appointment')]);
        return $request->expectsJson() ?
            response()->json([
                'message'     => __('Appointment created successfully.'),
                'appointment' => $appointment->refresh()->toFullCalendar(),
            ])
            : redirect()
            ->route('appointments.index')
            ->with(['success' => $message]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        if ($request->user()->cannot('update', $appointment)) {
            abort(403);
        }

        $data = $request->validated();
        
        $operatorIds = $request->input('operator_ids', [$request->input('operator_id')]);
        $learnerIds = $request->input('learner_ids', [$request->input('learner_id')]);

        $data['operator_id'] = $operatorIds[0];
        $data['learner_id'] = $learnerIds[0];

        $appointment->update(Arr::except($data, ['operator_ids', 'learner_ids']));
        $appointment->learners()->sync($learnerIds);
        $appointment->operators()->sync($operatorIds);

        $appointment->load(['learners', 'operators', 'discipline']);
        
        $learnerNames = $appointment->learners->pluck('full_name')->join(', ');
        $operatorNames = $appointment->operators->pluck('name')->join(', ');
        $appointment->update(['title' => $learnerNames . " (". $operatorNames .")"]);

        $message = __("The :resource was updated!", ['resource' => __('Appointment')]);
        return $request->expectsJson() ?
            response()->json([
                'message'     => $message,
                'appointment' => $appointment->toFullCalendar(),
            ])
            : redirect()
                ->route('appointments.index')
                ->with(['success' => $message]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Appointment $appointment)
    {
        if ($request->user()->cannot('delete', $appointment)) {
            abort(403);
        }

        $appointment->delete();

        $message = __("The :resource was deleted!", ['resource' => __('Appointment')]);
        return $request->expectsJson() ?
            response()->json([
                'message' => $message
            ])
            : redirect()
                ->route('appointments.index')
                ->with(['success' => $message]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        return view('appointments.show', compact('appointment'));
    }

    public function clearWeek(
        ClearWeeklyAppointmentsRequest $request,
        ClearWeeklyAppointments $action
    ) {
        $user = $request->user();
        $weekStart = Carbon::parse($request->validated('week_start'));

        $deleted = $action->execute($user, $weekStart);

        return response()->json([
            'message' => __('Appointments for the selected week have been cleared.'),
            'deleted' => $deleted,
        ]);
    }
}
