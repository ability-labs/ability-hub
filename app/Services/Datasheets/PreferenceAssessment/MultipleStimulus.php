<?php

namespace App\Services\Datasheets\PreferenceAssessment;

class MultipleStimulus extends PreferenceAssessmentAbstract
{
    const MINIMUM_ITEMS = 4;
    const SUGGESTED_ITEMS = 7;

    protected function processSessions(): void
    {
        foreach ($this->data['sessions'] as $session) {
            foreach ($session['answers']['rows'] as $index => [$order, $itemKey]) {
                if (isset($this->scores[$itemKey])) {
                    $this->scores[$itemKey] += count($session['answers']['rows']) - $index;
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

    protected function getColumnsSchema()
    {
        return [
            "Order",
            "Item"
        ];
    }
}
