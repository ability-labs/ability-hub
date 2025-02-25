<?php

namespace App\Services\Datasets\PreferenceAssessment;

use App\Services\Datasets\PreferenceAssessment\PreferenceAssessmentAbstract;

class MultipleStimulusWithReplacement extends PreferenceAssessmentAbstract
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
        $scores = collect($this->data['items'])->mapWithKeys(fn($item) => [$item['key'] => 0]);

        // Process sessions
        foreach ($this->data['sessions'] as $session) {
            foreach ($session['answers']['rows'] as [, , $choice]) {
                if (isset($scores[$choice])) {
                    $scores[$choice] += 1; // Count each choice as 1 point
                }
            }
        }

        // Sort results by total points descending
        $sortedScores = $scores->sortDesc()->map(function ($points, $key) use ($items) {
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
