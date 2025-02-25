<?php

namespace App\Services\Datasets\PreferenceAssessment;

class SingleItem extends PreferenceAssessmentAbstract
{
    const LEGEND = [
        [
            "key" => "I",
            "value" => "Interacts",
            "points" => 1,
        ],
        [
            "key" => "A",
            "value" => "Avoids",
            "points" => -1
        ],
        [
            "key" => "NA",
            "value" => "No Answer",
            "points" => 0,
        ],
    ];

    public function report(): array
    {
        $items = collect($this->data['items'])->keyBy('key');
        $legend = collect(self::LEGEND)->keyBy('key');

        // Initialize item scores
        $scores = collect($this->data['items'])->mapWithKeys(fn($item) => [$item['key'] => 0]);

        // Process sessions
        foreach ($this->data['sessions'] as $session) {
            foreach ($session['answers']['rows'] as [$itemKey, $answerKey]) {
                if (isset($legend[$answerKey]) && isset($scores[$itemKey])) {
                    $scores[$itemKey] += $legend[$answerKey]['points'];
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
