<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OperatorAvailabilityController extends Controller
{
    public function toggle(Request $request, Operator $operator)
    {

        $data = $request->validate([
            'slot_id' => ['required','uuid', Rule::exists('slots','id')],
            // facoltativo se vuoi vincolare alla disciplina mostrata in pagina
            'discipline_id' => ['nullable','uuid'],
        ]);

        /** @var Slot $slot */
        $slot = Slot::query()->findOrFail($data['slot_id']);

        if (!empty($data['discipline_id']) && $slot->discipline_id !== $data['discipline_id']) {
            return response()->json([
                'ok' => false,
                'message' => 'Lo slot non appartiene alla disciplina selezionata.'
            ], 422);
        }

        $attached = false;

        DB::transaction(function () use ($operator, $slot, &$attached) {
            $isAttached = $operator->slots()->whereKey($slot->id)->exists();
            if ($isAttached) {
                $operator->slots()->detach($slot->id);
                $attached = false;
            } else {
                $operator->slots()->attach($slot->id);
                $attached = true;
            }
        });

        return response()->json([
            'ok' => true,
            'attached' => $attached,
            'slot_id' => $slot->id,
        ]);
    }
}
