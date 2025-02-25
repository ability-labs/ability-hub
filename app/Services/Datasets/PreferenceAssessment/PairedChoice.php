<?php

namespace App\Services\Datasets\PreferenceAssessment;

class PairedChoice extends PreferenceAssessmentAbstract
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

        // Initialize item scores
        $scores = collect($this->data['items'])->mapWithKeys(fn($item) => [$item['key'] => 0]);

        // Process sessions
        foreach ($this->data['sessions'] as $session) {
            foreach ($session['answers']['rows'] as [$pair, $winner]) {
                if (isset($scores[$winner])) {
                    $scores[$winner] += 1; // Increment score for the winner
                }
            }
        }

        // Sort results by points descending, then by item name ascending
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
