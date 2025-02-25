<?php

namespace App\Services\Datasheets\PreferenceAssessment;

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
    const MINIMUM_ITEMS = 4;
    const SUGGESTED_ITEMS = 7;

    protected function processSessions(): void
    {
        $legend = collect(self::LEGEND)->keyBy('key')->toArray();
        foreach ($this->data['sessions'] as $session) {
            foreach ($session['answers']['rows'] as [$itemKey, $answerKey]) {
                if (isset($legend[$answerKey]) && isset($this->scores[$itemKey])) {
                    $this->scores[$itemKey] += $legend[$answerKey]['points'];
                }
            }
        }
    }
    protected function getMinimumItems(): int
    {
        return self::MINIMUM_ITEMS;
    }

    protected function getSuggestedItems(): int
    {
        return self::SUGGESTED_ITEMS;
    }
}
