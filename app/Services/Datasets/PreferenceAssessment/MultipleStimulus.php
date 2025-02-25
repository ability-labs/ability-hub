<?php

namespace App\Services\Datasets\PreferenceAssessment;

use App\Services\Datasets\PreferenceAssessment\PreferenceAssessmentAbstract;

class MultipleStimulus extends PreferenceAssessmentAbstract
{
    public function report(): array
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
            foreach ($session['answers']['rows'] as [$order, $itemKey, $score]) {
                if (isset($scores[$itemKey])) {
                    $scores[$itemKey] += $score;
                }
            }
        }

        // Sort results by total points descending
        $sortedScores = $scores->sortDesc()->map(function ($points, $key) use ($items) {
            return ['item' => $items[$key]['key'], 'points' => $points];
        })->values();

        /*

1 2
3 7
5 6
2 3
4 5
6 7
1 3

3 5
4 6
1 5
2 7
3 4
5 7
1 4

2 5
1 6
2 4
3 6
1 7
2 6
4 7
         */

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
