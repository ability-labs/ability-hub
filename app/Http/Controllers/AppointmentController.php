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
    public function store(StoreAppointmentRequest $request, \App\Actions\Appointments\UpsertAppointment $action)
    {
        $appointment = $action->execute($request->user(), $request->validated());

        $message = __("The :resource was created!", ['resource' => __('Appointment')]);
        return $request->expectsJson() ?
            response()->json([
                'message'     => __('Appointment created successfully.'),
                'appointment' => $appointment->toFullCalendar(),
            ])
            : redirect()
            ->route('appointments.index')
            ->with(['success' => $message]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment, \App\Actions\Appointments\UpsertAppointment $action)
    {
        if ($request->user()->cannot('update', $appointment)) {
            abort(403);
        }

        $appointment = $action->execute($request->user(), $request->validated(), $appointment);

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
