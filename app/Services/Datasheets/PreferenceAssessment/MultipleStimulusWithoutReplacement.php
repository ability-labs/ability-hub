<?php

namespace App\Services\Datasheets\PreferenceAssessment;

class MultipleStimulusWithoutReplacement extends PreferenceAssessmentAbstract
{
    protected function processSessions(): void
    {
        foreach ($this->data['sessions'] as $session) {
            foreach ($session['answers']['rows'] as [$sequenceOrder, , $choice]) {
                if (isset($this->scores[$choice])) {
                    $this->scores[$choice] += $sequenceOrder;
                }
            }
        }
    }

    protected function highScoresAreLowerRaked(): bool
    {
        return true;
    }
}
