<?php

namespace App\Http\Controllers;

use App\Actions\Learners\StoreLearnerAction;
use App\Actions\Learners\UpdateLearnerAction;
use App\Enums\PersonGender;
use App\Http\Requests\StoreLearnerRequest;
use App\Http\Requests\UpdateLearnerRequest;
use App\Models\Appointment;
use App\Models\Discipline;
use App\Models\Learner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class LearnerController extends Controller
{

    const SORTABLE_FIELDS = [
        'created_at',
        'birth_date',
        'firstname',
        'lastname',
        'weekly_hours',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $params = $request->validate([
            'search' => ['string','max:64'],
            'sort' => ['string', Rule::in(self::SORTABLE_FIELDS)],
            'sort_order' => ['string', 'in:DESC,ASC'],
        ]);

        $sort = $request->input('sort', 'created_at');
        $sort_order = $request->input('sort_order', 'DESC');

        if (!in_array($sort, self::SORTABLE_FIELDS)) {
            $sort = 'created_at';
        }

        if (!in_array($sort_order, ['ASC', 'DESC'])) {
            $sort_order = 'DESC';
        }

        $sortMapping = [
            'firstname' => 'first_name',
            'lastname' => 'last_name',
            'weekly_hours' => 'weekly_minutes',
            'created_at' => 'created_at',
            'birth_date' => 'birth_date',
        ];

        $column = $sortMapping[$sort] ?? 'created_at';

        $query = $request->user()->learners()
            ->orderBy($column, $sort_order);

        if (array_key_exists('search', $params)) {
            $query->where('first_name', 'like', '%' . $params['search'] . '%')
                ->orWhere('last_name', 'like', '%' . $params['search'] . '%');
        }

        $learners = $query->paginate(50);

        return view('learners.index', [
            'learners' => $learners,
            'sort' => $sort,
            'sort_order' => $sort_order,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $operators = $request->user()
            ->operators()
            ->select('id','name')
            ->orderBy('name')
            ->get();

        return view('learners.create', compact('operators'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLearnerRequest $request, StoreLearnerAction $storeLearnerAction)
    {
        $attributes = $request->validated();

        $operatorIds = collect($attributes['operator_ids'] ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();
        unset($attributes['operator_ids']);

        if (isset($attributes['weekly_hours']) && $attributes['weekly_hours'] !== '') {
            $hours = (float) str_replace(',', '.', $attributes['weekly_hours']);
            $attributes['weekly_minutes'] = (int) round($hours * 60); // 4.5 -> 270
        } else {
            $attributes['weekly_minutes'] = 0;
        }
        unset($attributes['weekly_hours']);

        $attributes['user_id'] = $request->user()->id;

        $storeLearnerAction->execute($attributes, $operatorIds);

        return redirect()->route('learners.index')->with('success');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Learner $learner)
    {
        $user = $request->user();
        if ($user->cannot('view', $learner)) {
            abort(403);
        }

        $user->load(['learners', 'operators']);

        return view('learners.show', [
            'learner' => $learner->load('appointments','datasheets','operators'),
            'events' => $learner->appointments->map(fn (Appointment $appointment) => $appointment->toFullCalendar()),

            'learners' => $user->learners,
            'operators' => $user->operators->load('disciplines'),
            'disciplines' => Discipline::all()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Learner $learner)
    {
        if ($request->user()->cannot('update', $learner)) {
            abort(403);
        }

        $learner->load(['operators.disciplines', 'slots']);

        $operators = $request->user()
            ->operators()
            ->select('id','name')
            ->orderBy('name')
            ->get();

        return view('learners.edit', ['learner' => $learner, 'operators' => $operators]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLearnerRequest $request, Learner $learner, UpdateLearnerAction $updateLearnerAction)
    {
        if ($request->user()->cannot('update', $learner)) {
            abort(403);
        }

        $attributes = $request->validated();

        $operatorIds = array_key_exists('operator_ids', $attributes)
            ? collect($attributes['operator_ids'])->filter()->unique()->values()->all()
            : null;
        unset($attributes['operator_ids']);

        if (array_key_exists('weekly_hours', $attributes)) {
            if ($attributes['weekly_hours'] === '' || $attributes['weekly_hours'] === null) {
                $attributes['weekly_minutes'] = 0;
            } else {
                $hours = (float) str_replace(',', '.', $attributes['weekly_hours']);
                $attributes['weekly_minutes'] = (int) round($hours * 60);
            }
            unset($attributes['weekly_hours']);
        }

        $updateLearnerAction->execute($learner, $attributes, $operatorIds);

        return redirect()->route('learners.index')->with('success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Learner $learner)
    {
        if ($request->user()->cannot('forceDelete', $learner)) {
            abort(403);
        }

        $learner->delete();

        return redirect()->route('learners.index')->with('success');
    }
}
