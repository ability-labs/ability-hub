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

    public function generatePairings(int $sessions): array
    {
        if (count($this->data['items']) < 7) {
            throw new \InvalidArgumentException("At least 7 items are required.");
        }
        $pairings = [];

        for ($i = 0; $i < $sessions; $i++) {
            $sessionPairs = $this->generateSessionPairs();
            $pairings[] = $sessionPairs;
        }

        return $pairings;
    }

    protected function generateSessionPairs(): array
    {
        $items = $this->data['items'];
        shuffle($items);

        $pairs = [];
        $used = [];

        while (count($used) < count($items)) {
            $pair = [];
            foreach ($items as $item) {
                if (!in_array($item, $used)) {
                    $pair[] = $item;
                    $used[] = $item;
                    if (count($pair) == 2) {
                        $pairs[] = $pair;
                        break;
                    }
                }
            }
        }

        return $pairs;
    }
}
