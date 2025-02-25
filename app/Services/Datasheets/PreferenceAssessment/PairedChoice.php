<?php

namespace App\Services\Datasheets\PreferenceAssessment;

class PairedChoice extends PreferenceAssessmentAbstract
{
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
}
