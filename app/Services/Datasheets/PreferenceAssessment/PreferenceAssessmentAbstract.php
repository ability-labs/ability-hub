<?php

namespace App\Services\Datasheets\PreferenceAssessment;

use App\Models\Reinforcer;
use App\Services\Datasheets\ReportAbstract;
use Illuminate\Support\Collection;

abstract class PreferenceAssessmentAbstract extends ReportAbstract
{
    public array $items;
    public array $scores;

    public function __construct(
        protected array $data
    )
    {
        $this->items = [];
        $this->scores = [];
        if (array_key_exists('items', $this->data)) {
            $this->items = collect($data['items'])
                ->keyBy('key')
                ->toArray();

            $this->scores = collect($data['items'])
                ->mapWithKeys(
                    fn($item) => [$item['key'] => 0]
                )->toArray();
        }
    }

    abstract protected function processSessions(): void;
    abstract protected function getMinimumItems(): int;
    abstract protected function getSuggestedItems(): int;

    public function report(): array
    {
        if (empty($this->data['items']) || count($this->data['items']) < $this->getMinimumItems()) {
            return ['columns' => ['Order', 'Item', 'Points'], 'rows' => []];
        }

        $this->processSessions();

        // Sort and rank
        return $this->generateRankedReport();
    }

    protected function generateRankedReport($highScoresAreLowerRaked = false): array
    {
        $sortedScores =
            collect($this->scores)
                ->{ $this->highScoresAreLowerRaked() ? 'sort' : 'sortDesc' }()
                ->map(
                    fn($points, $key)
                        => [
                            'item' => $this->items[$key]['key'],
                            'points' => $points
                        ]
                )
                ->values();

        $order = 1;
        $lastPoints = null;
        $rankedRows = [];

        foreach ($sortedScores as $index => $entry) {
            if ($lastPoints !== null && $lastPoints != $entry['points']) {
                $order = $index + 1;
            }
            $rankedRows[] = [$order, $entry['item'], $entry['points']];
            $lastPoints = $entry['points'];
        }

        return ['columns' => ['Order', 'Item', 'Points'], 'rows' => $rankedRows];
    }

    public function getReportTemplate(): array
    {
        return [
            'columns' => [
                "Order",
                "Item",
                "Points"
            ],
            "rows" => []
        ];
    }

    public function getInfo() :array
    {
        $info = [
            'minimum_items' => $this->getMinimumItems(),
            'suggested_items' => $this->getSuggestedItems(),

            'has_legend' => $this->hasLegend(),
            'has_sequences' => $this->hasSequences(),
            'has_items' => $this->hasItems(),
            'has_sessions' => $this->hasSessions(),

            'templates' => [
                'item' => $this->getItemTemplate(),
                'session' => $this->getSessionTemplate(),
                'report' => $this->getReportTemplate()
            ]
        ];
        if ($this->hasLegend())
            $info['legend'] = $this->getLegend();
        if ($this->hasSequences()) {
            $info['sequence_size'] = $this->getSequenceSize();
            $info['sequence_type'] = $this->getSequenceType();
            $info['sequence_strategy'] = $this->getSequenceStrategy();
        }
        return $info;
    }
    public function getDatasetTemplate(): array
    {
        return [
            "items" => [],
            "sessions" => []
        ];
    }

    public function getSessionTemplate(): array
    {
        return [
            'datetime' => '',
            'answers' => [
                'columns' => $this->getColumnsSchema(),
                "rows" => []
            ]
        ];
    }

    public function getItemTemplate(): array
    {
        return [
            'id' => '',
            'key' => '',
            'name' => ''
        ];
    }

    protected function highScoresAreLowerRaked(): bool
    {
        return false;
    }

    protected function getColumnsSchema()
    {
        return [
            "Item",
            "Answer"
        ];
    }

    public function hasItems(): bool
    {
        return true;
    }

    public function hasSessions(): bool
    {
        return true;
    }

    public function hasLegend(): bool
    {
        return false;
    }

    public function getLegend(): ?array
    {
        return null;
    }


    public function hasSequences(): bool
    {
        return false;
    }

    public function getSequenceSize(): ?int
    {
        return null;
    }

    public function getSequenceType(): ?string
    {
        return null;
    }

    public function getSequenceStrategy(): ?string
    {
        return null;
    }

    public function mockData(): array
    {
        $data = [];
        if ($this->hasItems()) {
            $data['items'] = $this->pickRandomItems();
        }

        if ($this->hasSessions()) {
            for ($i = 1; $i <= rand(2,4); $i++) {
                $data['sessions'][] = $this->mockSessionData($data['items'], $i);
            }
        }

        return $data;
    }

    protected function mockSessionData(array $items, int $i): array
    {
        $columns_schema = $this->getColumnsSchema();
        $random_items = $items;
        shuffle($random_items);
        return [
            'datetime' => now(),
            'answers' => [
                'columns' => $columns_schema,
                'rows' => collect($items)->map(function ($item, $index) use ($random_items, $columns_schema) {
                    return Collection::times(count($columns_schema), function (int $number) use ($random_items, $index, $item, $columns_schema) {
                        $column_name = $columns_schema[$number-1];
                        switch ($column_name) {
                            case "Order":
                                return $index + 1;
                            case "Item":
                                return (string) $random_items[count($random_items) - ($index+1)]['key'];
                            default:
                                return '';
                        }
                    })->values()->toArray();
                })
            ]
        ];
    }

    private function pickRandomItems(): array
    {
        $items = [];
        for ($i = 1; $i <= $this->getSuggestedItems(); $i++) {
            $random_reinforcer = Reinforcer::query()->inRandomOrder()->first();
            $items[] = [
                "id" => $random_reinforcer->id,
                "key" => $i,
                "name" => $random_reinforcer->name,
                "category" => $random_reinforcer->category,
                "subcategory" => $random_reinforcer->subcategory,
            ];
        }
        return $items;
    }
}
