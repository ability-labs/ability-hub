<?php

namespace App\Http\Controllers\Api\Appointments;

use App\Actions\Appointments\DuplicateWeeklyAppointments;
use App\Http\Controllers\Controller;
use App\Http\Requests\DuplicateWeeklyAppointmentsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DuplicateWeekController extends Controller
{
    public function __invoke(
        DuplicateWeeklyAppointmentsRequest $request,
        DuplicateWeeklyAppointments $action
    )
    {
        $user = $request->user();
        $weekStart = Carbon::parse($request->validated('week_start'));
        $weekEnd = Carbon::parse($request->validated('week_end'));

        $created = $action->execute($user, $weekStart, $weekEnd);

        return response()->json([
            'message' => __('Appointments duplicated successfully.'),
            'created' => $created,
        ]);
    }

}
