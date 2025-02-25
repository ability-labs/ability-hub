<?php

namespace App\Services\Datasheets\PreferenceAssessment;

class MultipleStimulusWithoutReplacement extends PreferenceAssessmentAbstract
{

    function report(): array
    {
        if (empty($this->data['items'])) {
            return [
                'columns' => ['Order', 'Item', 'Points'],
                'rows' => []
            ];
        }

        $items = collect($this->data['items'])->keyBy('key');
        $scores = collect($this->data['items'])->mapWithKeys(fn($item) => [$item['key'] => 0])->toArray();

        // Process sessions
        foreach ($this->data['sessions'] as $session) {
            foreach ($session['answers']['rows'] as [$sequenceOrder, $proposedSequence, $choice]) {
                $scores[$choice] += $sequenceOrder;
            }
        }

        // Sort results by total points descending
        $sortedScores = collect($scores)->sort()->map(function ($points, $key) use ($items) {
            return ['item' => $items[$key]['key'], 'points' => $points];
        })->values();

        // Assign order based on ranking logic
        $order = 1;
        $lastPoints = null;
        $rankedRows = [];

        foreach ($sortedScores as $index => $entry) {
            if ($lastPoints !== null && $lastPoints != $entry['points']) {
                $order = $index + 1;
            }
            $rankedRows[] = [$order, $entry['item'], $entry['points']];
            $lastPoints = $entry['points'];
        }

        return [
            'columns' => ['Order', 'Item', 'Points'],
            'rows' => $rankedRows,
        ];
    }
}
