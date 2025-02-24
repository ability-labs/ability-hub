<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Discipline;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $events = $user
            ->appointments()
            ->with(['learner', 'operator', 'discipline'])
            ->get()
            ->map(fn (Appointment $appointment) => $appointment->toFullCalendar());

        $operators = $user->operators()->with('disciplines')->get();
        $learners  = $user->learners()->get();
        $disciplines = Discipline::all();

        return view(
            'appointments.index',
            compact('events', 'operators', 'learners', 'disciplines')
        );
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
}
