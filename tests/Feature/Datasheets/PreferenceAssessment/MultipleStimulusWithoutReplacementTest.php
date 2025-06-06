<?php

namespace Tests\Feature\Datasheets\PreferenceAssessment;

use App\Services\Datasheets\PreferenceAssessment\MultipleStimulusWithoutReplacement;
use Carbon\Carbon;

class MultipleStimulusWithoutReplacementTest extends PreferenceAssessmentTestCase
{
    public function test_it_will_report_multiple_stimulus_preference_assessment_results_with_multiple_session_dataset()
    {
        $test_date = Carbon::parse($this->faker->dateTimeThisDecade());
        $dataset = self::EMPTY_DATASET;
        $dataset['items'] = [
            [
                'key' => '1',
                'name' => 'Item 1'
            ],
            [
                'key' => '2',
                'name' => 'Item 2'
            ],
            [
                'key' => '3',
                'name' => 'Item 3'
            ],
            [
                'key' => '4',
                'name' => 'Item 4'
            ],
            [
                'key' => '5',
                'name' => 'Item 5'
            ],
            [
                'key' => '6',
                'name' => 'Item 6'
            ],
        ];
        $dataset['sessions'] = [
            [
                'datetime' => $test_date,
                'answers' => [
                    'columns' => [
                        "Sequence Order",
                        "Proposed Sequence",
                        "Choice",
                    ],
                    "rows" => [
                        [1, ["1", "2", "3", "4", "5", "6"], "1"],
                        [2, ["3", "4", "5", "6", "2"], "2"],
                        [3, ["4", "5", "6", "3"], "3"],
                        [4, ["5", "6", "4"], "4"],
                        [5, ["6", "5"], "6"],
                        [6, ["5"], "5"],
                    ]
                ]
            ],
                [
                    'datetime' => $test_date,
                    'answers' => [
                        'columns' => [
                            "Sequence Order",
                            "Proposed Sequence",
                            "Choice",
                        ],
                        "rows" => [
                            [1, ["1", "2", "3", "4", "5", "6"], "3"],
                            [2, ["2", "4", "5", "6", "1"], "1"],
                            [3, ["4", "5", "6", "2"], "2"],
                            [4, ["5", "6", "4"], "4"],
                            [5, ["6", "5"], "5"],
                            [6, ["6"], "6"],
                        ]
                    ]
                ],
                [
                    'datetime' => $test_date,
                    'answers' => [
                        'columns' => [
                            "Sequence Order",
                            "Proposed Sequence",
                            "Choice",
                        ],
                        "rows" => [
                            [1, ["6", "5", "4", "3", "2", "1"], "3"],
                            [2, ["5", "4", "2", "1", "6"], "2"],
                            [3, ["4", "1", "6", "5"], "1"],
                            [4, ["6", "5", "4"], "4"],
                            [5, ["5", "6"], "5"],
                            [6, ["6"], "6"],
                        ]
                    ]
                ]
        ];


        $expected_result = [
            'columns' => [
                'Order',
                "Item",
                "Points"
            ],
            "rows" => [
                [1, "3", 3 + 1 + 1], // 5 points
                [2, "1", 1 + 2 + 3], // 6 points
                [3, "2", 2 + 3 + 2], // 7 points
                [4, "4", 4 + 4 + 4], // 12 points
                [5, "5", 5 + 5 + 6], // 16 points
                [6, "6", 5 + 6 + 6], // 17 points
            ]
        ];

        $dataset_instance = new MultipleStimulusWithoutReplacement($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }
}
