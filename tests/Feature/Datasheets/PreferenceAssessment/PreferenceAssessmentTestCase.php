<?php

namespace Tests\Feature\Datasheets\PreferenceAssessment;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PreferenceAssessmentTestCase extends TestCase
{
    use WithFaker;

    const EMPTY_DATASET = [
        'items' => [],
        'legend' => [],
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
