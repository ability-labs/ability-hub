<?php

namespace App\Services\Datasets\PreferenceAssessment;

abstract class PreferenceAssessmentAbstract
{
    public function __construct(
        protected array $data
    )
    {
    }

    public function getReportTemplate(): array
    {
        return [
            'columns' => [
                'Order',
                "Item",
                "Points"
            ],
            "rows" => [
            ]
        ];
    }

    abstract function report(): array;

}
