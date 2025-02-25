<?php

namespace App\Services\Datasheets\PreferenceAssessment;

class MultipleStimulus extends PreferenceAssessmentAbstract
{
    protected function processSessions(): void
    {
        foreach ($this->data['sessions'] as $session) {
            foreach ($session['answers']['rows'] as [$order, $itemKey, $score]) {
                if (isset($this->scores[$itemKey])) {
                    $this->scores[$itemKey] += $score;
                }
            }
        }
    }
}
