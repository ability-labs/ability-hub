<?php

namespace App\Http\Controllers;

use App\Actions\Appointments\PlanWeeklyAppointments;
use App\Models\Learner;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WeeklyPlanController extends Controller
{
    public function store(Request $request, PlanWeeklyAppointments $action) {
        $user = $request->user();
        $start_date = Carbon::parse($request->input('starts_at'));
        $learners = Learner::findMany($request->input('learners'));
        $result = $action->execute($user, $start_date, $learners);
        return response()->json($result);
    }
}
