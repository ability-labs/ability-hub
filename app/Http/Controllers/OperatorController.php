<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOperatorRequest;
use App\Http\Requests\UpdateOperatorRequest;
use App\Models\Appointment;
use App\Models\Discipline;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OperatorController extends Controller
{
    const VALIDATION_RULES = [
        'name'        => 'required|string|max:255',
        'vat_id'      => 'nullable|string|max:50',
        'disciplines' => 'nullable|array',
        'disciplines.*' => 'uuid|exists:disciplines,id',
    ];

    const SORTABLE_FIELDS = [
        'created_at',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $params = $request->validate([
            'search'     => ['string','max:64'],
            'sort'       => ['string', Rule::in(self::SORTABLE_FIELDS)],
            'sort_order' => ['string', 'in:DESC,ASC'],
        ]);

        $sort = array_key_exists('sort', $params) ? $params['sort'] : 'created_at';
        $sort_order = array_key_exists('sort', $params) ? $params['sort_order'] : 'DESC';

        $query = $request->user()->operators()->orderBy($sort, $sort_order);

        if (array_key_exists('search', $params)) {
            $query->where('name', 'like', '%' . $params['search'] . '%');
        }

        $operators = $query->paginate(25);

        return view('operators.index', [
            'operators'      => $operators,
            'sortable_fields'=> self::SORTABLE_FIELDS,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $disciplines = Discipline::all();
        return view('operators.create', compact('disciplines'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOperatorRequest $request)
    {
        $attributes = $request->validate(self::VALIDATION_RULES);
        $attributes['user_id'] = $request->user()->id;

        $operator = Operator::create([
            'name'    => $attributes['name'],
            'vat_id'  => $attributes['vat_id'] ?? null,
            'user_id' => $attributes['user_id'],
        ]);

        if (!empty($attributes['disciplines'])) {
            $operator->disciplines()->sync($attributes['disciplines']);
        }

        return redirect()->route('operators.index')
            ->with('success', __('Operator created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Operator $operator)
    {
        $user = $request->user();
        if ($user->cannot('view', $operator)) {
            abort(403);
        }

        $user->load(['learners', 'operators']);

        return view('operators.show', [
            'operator' => $operator->load('appointments'),
            'events' => $operator->appointments->map(fn (Appointment $appointment) => $appointment->toFullCalendar()),

            'learners' => $user->learners,
            'operators' => $user->operators->load('disciplines'),
            'disciplines' => Discipline::all()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Operator $operator)
    {
        if ($request->user()->cannot('update', $operator)) {
            abort(403);
        }

        $disciplines = Discipline::all();
        return view('operators.edit', compact('operator', 'disciplines'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOperatorRequest $request, Operator $operator)
    {
        if ($request->user()->cannot('update', $operator)) {
            abort(403);
        }

        $attributes = $request->validate(self::VALIDATION_RULES);

        $operator->update([
            'name'   => $attributes['name'],
            'vat_id' => $attributes['vat_id'] ?? null,
        ]);

        if (isset($attributes['disciplines'])) {
            $operator->disciplines()->sync($attributes['disciplines']);
        } else {
            $operator->disciplines()->detach();
        }

        return redirect()->route('operators.index')->with('success', __('Operator updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Operator $operator)
    {
        if ($request->user()->cannot('delete', $operator)) {
            abort(403);
        }

        $operator->delete();

        return redirect()->route('operators.index')->with('success', __('Operator deleted successfully.'));
    }
}
