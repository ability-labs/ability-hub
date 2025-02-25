<?php

namespace App\Services\Datasheets\PreferenceAssessment;

class PairedChoice extends PreferenceAssessmentAbstract
{
    const MINIMUM_ITEMS = 4;
    const SUGGESTED_ITEMS = 6;

    protected function processSessions(): void
    {
        foreach ($this->data['sessions'] as $session) {
            foreach ($session['answers']['rows'] as [, $winner]) {
                if (isset($this->scores[$winner])) {
                    $this->scores[$winner] += 1;
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
