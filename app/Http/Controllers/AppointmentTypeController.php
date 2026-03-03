<?php

namespace App\Http\Controllers;

use App\Models\AppointmentType;
use Illuminate\Http\Request;

class AppointmentTypeController extends Controller
{
    const VALIDATION_RULES = [
        'name' => 'required|array',
        'name.it' => 'required|string|max:255',
        'color' => 'nullable|string|regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $appointmentTypes = AppointmentType::orderBy('created_at', 'desc')->paginate(25);
        return view('appointment-types.index', compact('appointmentTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('appointment-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $attributes = $request->validate(self::VALIDATION_RULES);

        AppointmentType::create([
            'name' => $attributes['name'],
            'color' => $attributes['color'] ?? null,
        ]);

        return redirect()->route('appointment-types.index')->with('success', __('Appointment type created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(AppointmentType $appointmentType)
    {
        return redirect()->route('appointment-types.edit', $appointmentType);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AppointmentType $appointmentType)
    {
        return view('appointment-types.edit', compact('appointmentType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AppointmentType $appointmentType)
    {
        $attributes = $request->validate(self::VALIDATION_RULES);

        $appointmentType->update([
            'name' => $attributes['name'],
            'color' => $attributes['color'] ?? null,
        ]);

        return redirect()->route('appointment-types.index')->with('success', __('Appointment type updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AppointmentType $appointmentType)
    {
        $appointmentType->delete();

        return redirect()->route('appointment-types.index')->with('success', __('Appointment type deleted successfully.'));
    }
}
