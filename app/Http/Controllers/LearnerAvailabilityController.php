<?php

namespace App\Http\Controllers;

use App\Models\Learner;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LearnerAvailabilityController extends Controller
{
    public function toggle(Request $request, Learner $learner)
    {
        if ($request->user()->cannot('update', $learner)) {
            abort(403);
        }

        $data = $request->validate([
            'slot_id' => ['required','uuid', Rule::exists('slots','id')],
            'discipline_id' => ['nullable','uuid'],
        ]);

        /** @var Slot $slot */
        $slot = Slot::query()->findOrFail($data['slot_id']);

        // opzionale: se ci arriva discipline_id, deve combaciare
        if (!empty($data['discipline_id']) && $slot->discipline_id !== $data['discipline_id']) {
            return response()->json(['ok'=>false,'message'=>'Slot/discipline mismatch'], 422);
        }

        // vincolo: la disciplina dello slot deve essere tra quelle dell’operatore del learner
        $allowedDisciplineIds = $learner->operator?->disciplines->pluck('id')->all() ?? [];
        if (!in_array($slot->discipline_id, $allowedDisciplineIds, true)) {
            return response()->json(['ok'=>false,'message'=>'Slot not allowed for this learner'], 422);
        }

        $attached = false;

        DB::transaction(function () use ($learner, $slot, &$attached) {
            $isAttached = $learner->slots()->whereKey($slot->id)->exists();
            if ($isAttached) {
                $learner->slots()->detach($slot->id);
                $attached = false;
            } else {
                $learner->slots()->attach($slot->id);
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
