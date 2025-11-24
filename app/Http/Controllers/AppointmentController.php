<?php

namespace App\Http\Controllers;

use App\Actions\Appointments\ClearWeeklyAppointments;
use App\Http\Requests\ClearWeeklyAppointmentsRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
        $data['title'] = 'temp';
        $data['user_id'] = $request->user()->id;

        $appointment = Appointment::create($data);
        $appointment->load(['learner', 'operator', 'discipline']);
        $appointment->update(['title' => $appointment->learner->full_name . " (". $appointment->operator->name .")"]);

        $message = __("The :resource was created!", ['resource' => __('Appointment')]);
        return $request->ajax() ?
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
        $appointment->update($data);
        $appointment->load(['learner', 'operator', 'discipline']);
        $appointment->update(['title' => $appointment->learner->full_name . " (". $appointment->operator->name .")"]);

        $message = __("The :resource was updated!", ['resource' => __('Appointment')]);
        return $request->ajax() ?
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
        return $request->ajax() ?
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
