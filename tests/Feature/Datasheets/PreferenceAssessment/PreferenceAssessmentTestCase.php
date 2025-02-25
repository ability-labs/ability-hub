<?php

namespace Tests\Feature\Datasheets\PreferenceAssessment;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PreferenceAssessmentTestCase extends TestCase
{
    use WithFaker;

    const EMPTY_DATASET = [
        'items' => [],
        'legend' => [
            [
                "key" => "I",
                "value" => "Interacts",
                "points" => 1,
            ],
            [
                "key" => "A",
                "value" => "Avoids",
                "points" => -1
            ],
            [
                "key" => "NA",
                "value" => "No Answer",
                "points" => 0,
            ],
        ],
        'sessions' => [

        ]
    ];
    const EXPECTED_TEMPLATE = [
        'columns' => [
            'Order',
            "Item",
            "Points"
        ],
        "rows" => [
        ]
    ];
}
