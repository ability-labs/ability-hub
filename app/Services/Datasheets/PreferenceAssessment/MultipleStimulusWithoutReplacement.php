<?php

namespace App\Services\Datasheets\PreferenceAssessment;

class MultipleStimulusWithoutReplacement extends PreferenceAssessmentAbstract
{
    const MINIMUM_ITEMS = 5;
    const SUGGESTED_ITEMS = 7;

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
            "Sequence Order",
            "Proposed Items",
            "Choice",
        ];
    }

    public function hasSequences(): bool
    {
        return true;
    }

    public function getSequenceSize(): ?int
    {
        return 0;
    }

    public function getSequenceType(): ?string
    {
        return "decremental";
    }

    public function getSequenceStrategy(): ?string
    {
        return "remove-chosen-move-first-at-end";
    }
}
