<?php

namespace Tests\Feature\Datasheets\PreferenceAssessment;

use App\Services\Datasheets\PreferenceAssessment\FreeOperantObservation;
use App\Services\Datasheets\PreferenceAssessment\SingleItem;
use Carbon\Carbon;
use Tests\Feature\Datasheets\PreferenceAssessment\PreferenceAssessmentTestCase;

class FreeOperantObservationTest extends PreferenceAssessmentTestCase
{

    public function test_it_will_report_single_item_preference_assessment_results_with_complete_dataset()
    {
        $test_date = Carbon::parse($this->faker->dateTimeThisDecade());
        $dataset = self::EMPTY_DATASET;
        $dataset['items'] = [
            [
                'key' => 'A',
                'name' => 'Item A'
            ],
            [
                'key' => 'B',
                'name' => 'Item B'
            ],
            [
                'key' => 'C',
                'name' => 'Item C'
            ],
            [
                'key' => 'D',
                'name' => 'Item D'
            ],
        ];
        $dataset['sessions'] = [
            [
                'datetime' => $test_date,
                'answers' => [
                    'columns' => [
                        "Item",
                        "Answer"
                    ],
                    "rows" => [
                        ["A", "DNA"],
                        ["B", "A"],
                        ["C", "EW"],
                        ["D", "DNA"]
                    ]
                ]
            ],
            [
                'datetime' => $test_date->addMinutes(3),
                'answers' => [
                    'columns' => [
                        "Item",
                        "Answer"
                    ],
                    "rows" => [
                        ["A", "A"],
                        ["B", "DNA"],
                        ["C", "EW"],
                        ["D", "DNA"]
                    ]
                ]
            ],
            [
                'datetime' => $test_date->addMinutes(6),
                'answers' => [
                    'columns' => [
                        "Item",
                        "Answer"
                    ],
                    "rows" => [
                        ["A", "EW"],
                        ["B", "A"],
                        ["C", "A"],
                        ["D", "A"]
                    ]
                ]
            ],
        ];
        $expected_result = [
            'columns' => [
                'Order',
                "Item",
                "Points"
            ],
            "rows" => [
                [1, "C", 5],
                [2, "A", 3],
                [3, "B", 2],
                [4, "D", 1]
            ]
        ];

        $dataset_instance = new FreeOperantObservation($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }
}
