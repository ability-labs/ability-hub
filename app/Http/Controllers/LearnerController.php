<?php

namespace App\Http\Controllers;

use App\Actions\StoreLearnerAction;
use App\Actions\UpdateLearnerAction;
use App\Http\Requests\StoreLearnerRequest;
use App\Http\Requests\UpdateLearnerRequest;
use App\Models\Learner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LearnerController extends Controller
{
    const VALIDATION_RULES = [
        'first_name' => 'string:128',
        'last_name' => 'string:128',
        'birth_date' => 'date',
    ];
    const SORTABLE_FIELDS = [
        'created_at',
        'birth_date'
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

        return view('learners.index', ['learners' => $learners, 'sortable_fields' => self::SORTABLE_FIELDS]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('learners.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLearnerRequest $request, StoreLearnerAction $storeLearnerAction)
    {
        $attributes = $request->validate(self::VALIDATION_RULES);

        $attributes['user_id'] = $request->user()->id;
        $storeLearnerAction->execute($attributes);

        return redirect()->route('learners.index')->with('success');
    }

    /**
     * Display the specified resource.
     */
    public function show(Learner $learner)
    {
        return view('learners.show', ['learner' => $learner]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Learner $learner)
    {
        return view('learners.edit', ['learner' => $learner]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLearnerRequest $request, Learner $learner, UpdateLearnerAction $updateLearnerAction)
    {
        $attributes = $request->validate(self::VALIDATION_RULES);
        $updateLearnerAction->execute($learner, $attributes);

        return redirect()->route('learners.index')->with('success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Learner $learner)
    {
        $learner->delete();

        return redirect()->route('learners.index')->with('success');
    }
}
