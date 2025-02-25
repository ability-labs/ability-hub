<?php

namespace App\Services\Datasheets\PreferenceAssessment;

class MultipleStimulusWithReplacement extends PreferenceAssessmentAbstract
{
    const MINIMUM_ITEMS = 5;
    const SUGGESTED_ITEMS = 7;

    protected function processSessions(): void
    {
        foreach ($this->data['sessions'] as $session) {
            foreach ($session['answers']['rows'] as [, , $choice]) {
                if (isset($this->scores[$choice])) {
                    $this->scores[$choice] += 1;
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
