<?php

namespace Datasheets\PreferenceAssessment;

use App\Services\Datasets\PreferenceAssessment\MultipleStimulus;
use App\Services\Datasets\PreferenceAssessment\MultipleStimulusWithoutReplacement;
use App\Services\Datasets\PreferenceAssessment\MultipleStimulusWithReplacement;
use Carbon\Carbon;
use Tests\Feature\Datasheets\PreferenceAssessment\PreferenceAssessmentTestCase;
use Tests\TestCase;

class MultipleStimulusWithReplacementTest extends PreferenceAssessmentTestCase
{
    public function test_it_will_report_multiple_stimulus_with_replacement_preference_assessment_results_with_single_session_dataset()
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
                        [1, ["1", "2", "3"], "1"],
                        [1, ["1", "4", "5"], "1"],
                        [1, ["1", "6", "3"], "3"],
                        [1, ["2", "5", "4"], "5"],
                        [1, ["6", "5", "3"], "3"],
                        [1, ["2", "4", "3"], "2"],
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
                [1, "1", 2],
                [1, "3", 2],
                [3, "2", 1],
                [3, "5", 1],
                [5, "4", 0],
                [5, "6", 0],
            ]
        ];

        $dataset_instance = new MultipleStimulusWithReplacement($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }
    public function test_it_will_report_multiple_stimulus_with_replacement_preference_assessment_results_with_multiple_sessions_dataset()
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
                        [1, ["1", "2", "3"], "1"],
                        [1, ["1", "4", "5"], "1"],
                        [1, ["1", "6", "3"], "3"],
                        [1, ["2", "5", "4"], "5"],
                        [1, ["6", "5", "3"], "3"],
                        [1, ["2", "4", "3"], "2"],
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
                        [1, ["3", "2", "1"], "1"],
                        [1, ["1", "4", "5"], "5"],
                        [1, ["2", "6", "5"], "2"],
                        [1, ["2", "1", "4"], "1"],
                        [1, ["6", "1", "3"], "3"],
                        [1, ["5", "4", "3"], "3"],
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
                [1, "1", 4],
                [1, "3", 4],
                [3, "2", 2],
                [3, "5", 2],
                [5, "4", 0],
                [5, "6", 0],
            ]
        ];

        $dataset_instance = new MultipleStimulusWithReplacement($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }
}
