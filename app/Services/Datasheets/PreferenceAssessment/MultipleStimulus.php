<?php

namespace App\Services\Datasheets\PreferenceAssessment;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

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

    protected function mockSessionData(mixed $items, int $i): array
    {
        $columns_schema = $this->getColumnsSchema();
        $random_items = $items;
        shuffle($random_items);
        return [
            'datetime' => now(),
            'answers' => [
                'columns' => $columns_schema,
                'rows' => collect($items)->map(function ($item, $itemIndex) use ($i, $random_items, $columns_schema) {
                    return Collection::times(count($columns_schema), function (int $number) use ($i, $random_items, $itemIndex, $item, $columns_schema) {
                        $column_name = $columns_schema[$number-1];
                        $order = $itemIndex + 1;;
                        switch ($column_name) {
                            case "Order":
                                return $order;
                            case "Item":
                                return (string) $item['key'];
                            default:
                                return count($this->items) - $i;
                        }
                    })->values()->toArray();
                })
            ]
        ];
    }
}
