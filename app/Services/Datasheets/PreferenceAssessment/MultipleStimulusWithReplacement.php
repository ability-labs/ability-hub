<?php

namespace App\Services\Datasheets\PreferenceAssessment;

class MultipleStimulusWithReplacement extends PreferenceAssessmentAbstract
{
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
}
