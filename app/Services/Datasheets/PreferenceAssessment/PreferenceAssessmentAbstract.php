<?php

namespace App\Services\Datasheets\PreferenceAssessment;

use App\Services\Datasheets\ReportAbstract;

abstract class PreferenceAssessmentAbstract extends ReportAbstract
{
    protected array $items;
    protected array $scores;

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
        return $info;
    }
    public function getDatasetTemplate(): array
    {
        return [
            "items" => [$this->getItemTemplate()],
            "sessions" => [$this->getSessionTemplate()]
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

}
