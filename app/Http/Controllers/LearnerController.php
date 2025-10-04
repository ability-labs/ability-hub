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

        $sort = array_key_exists('sort', $params) ? $params['sort'] : 'created_at';
        $sort_order = array_key_exists('sort', $params) ? $params['sort_order'] : 'DESC';

        $query = $request->user()->learners()
            ->orderBy($sort, $sort_order);

        if (array_key_exists('search', $params)) {
            $query->where('first_name', 'like', '%' . $params['search'] . '%')
                ->orWhere('last_name', 'like', '%' . $params['search'] . '%');
        }

        $learners = $query->paginate(25);

        return view('learners.index', [
            'learners' => $learners,
            'sortable_fields' => self::SORTABLE_FIELDS,
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
            $attributes['weekly_minutes'] = null;
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
                $attributes['weekly_minutes'] = $learner->weekly_minutes;
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
