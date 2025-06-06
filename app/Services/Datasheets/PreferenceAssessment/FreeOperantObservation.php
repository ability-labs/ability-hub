<?php

namespace App\Services\Datasheets\PreferenceAssessment;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class FreeOperantObservation extends PreferenceAssessmentAbstract
{
    const LEGEND = [
        [
            "key" => "A",
            "value" => "Approached",
            "points" => 1,
        ],
        [
            "key" => "DNA",
            "value" => "Did Not Approached",
            "points" => 0
        ],
        [
            "key" => "EW",
            "value" => "Engaged With",
            "points" => 2,
        ],
    ];
    const SUGGESTED_ITEMS = 5;
    const MINIMUM_ITEMS = 0;

    protected function processSessions(): void
    {
        $legend = collect(self::LEGEND)->keyBy('key');

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


    public function hasLegend(): bool
    {
        return true;
    }

    public function getLegend(): ?array
    {
        return self::LEGEND;
    }

    protected function mockSessionData(mixed $items, int $i): array
    {
        $columns_schema = $this->getColumnsSchema();
        $random_items = $items;
        shuffle($random_items);
        return [
            'datetime' => now(),
            'answers' => [
                'columns' => $columns_schema,
                'rows' => collect($items)->map(function ($item, $itemIndex) use ($random_items, $columns_schema) {
                    return Collection::times(count($columns_schema), function (int $number) use ($random_items, $itemIndex, $item, $columns_schema) {
                        $column_name = $columns_schema[$number-1];
                        switch ($column_name) {
                            case "Answer":
                                return Arr::random($this->getLegend())['key'];
                            case "Item":
                                return (string) $item['key'];
                            default:
                                return '';
                        }
                    })->values()->toArray();
                })
            ]
        ];
    }
}
