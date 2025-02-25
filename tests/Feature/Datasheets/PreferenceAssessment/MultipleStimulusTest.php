<?php

namespace Tests\Feature\Datasheets\PreferenceAssessment;

use App\Services\Datasets\PreferenceAssessment\MultipleStimulus;
use App\Services\Datasets\PreferenceAssessment\PairedChoice;
use App\Services\Datasets\PreferenceAssessment\SingleItem;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MultipleStimulusTest extends TestCase
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

    public function test_it_will_return_expected_report_template()
    {
        $expected_result = self::EXPECTED_TEMPLATE;
        $dataset_class = new PairedChoice([]);
        $this->assertEquals($expected_result, $dataset_class->getReportTemplate());
    }
    public function test_it_will_report_multiple_stimulus_preference_assessment_results_with_empty_dataset()
    {
        $dataset = self::EMPTY_DATASET;
        $expected_result = self::EXPECTED_TEMPLATE;

        $dataset_instance = new PairedChoice($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }
    public function test_it_will_report_multiple_stimulus_preference_assessment_results_with_empty_sessions_dataset()
    {
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

        $expected_result = [
            'columns' => [
                'Order',
                "Item",
                "Points"
            ],
            "rows" => [
                [1, "A", 0],
                [1, "B", 0],
                [1, "C", 0],
                [1, "D", 0]
            ]
        ];

        $dataset_instance = new SingleItem($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }
    public function test_it_will_report_multiple_stimulus_preference_assessment_results_with_empty_items_dataset()
    {
        $test_date = Carbon::parse($this->faker->dateTimeThisDecade());
        $dataset = self::EMPTY_DATASET;
        $dataset['sessions'] = [
            [
                'datetime' => $test_date,
                'answers' => [
                    'columns' => [
                        "Pair",
                        "Answer"
                    ],
                    "rows" => [
                        [["1","2"], "1"],
                        [["3", "7"], "3"],
                        [["5", "6"], "6"],
                        [["2", "3"], "3"],
                        [["4", "5"], "4"],
                        [["6", "7"], "7"],
                        [["1", "3"], "1"],
                    ]
                ]
            ],
            [
                'datetime' => $test_date,
                'answers' => [
                    'columns' => [
                        "Pair",
                        "Answer"
                    ],
                    "rows" => [
                        [["3","5"], "3"],
                        [["4", "6"], "6"],
                        [["1", "5"], "1"],
                        [["2", "7"], "7"],
                        [["3", "4"], "3"],
                        [["5", "7"], "5"],
                        [["1", "4"], "1"],
                    ]
                ]
            ],
            [
                'datetime' => $test_date,
                'answers' => [
                    'columns' => [
                        "Pair",
                        "Answer"
                    ],
                    "rows" => [
                        [["2","5"], "2"],
                        [["1", "6"], "1"],
                        [["2", "4"], "4"],
                        [["3", "6"], "3"],
                        [["1", "7"], "1"],
                        [["2", "6"], "2"],
                        [["4", "7"], "7"],
                    ]
                ]
            ],
        ];
        $expected_result = self::EXPECTED_TEMPLATE;

        $dataset_instance = new SingleItem($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }

    public function test_it_will_report_multiple_stimulus_preference_assessment_results_with_single_session_dataset()
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
            [
                'key' => '7',
                'name' => 'Item 7'
            ],
        ];
        $dataset['sessions'] = [
            [
                'datetime' => $test_date,
                'answers' => [
                    'columns' => [
                        "Order",
                        "Item",
                        "Score"
                    ],
                    "rows" => [
                        [1, "2", 7],
                        [2, "4", 6],
                        [3, "3", 5],
                        [4, "5", 4],
                        [5, "1", 3],
                        [6, "6", 2],
                        [7, "7", 1],
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
                [1, "2", 7],
                [2, "4", 6],
                [3, "3", 5],
                [4, "5", 4],
                [5, "1", 3],
                [6, "6", 2],
                [7, "7", 1],
            ]
        ];

        $dataset_instance = new MultipleStimulus($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }

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
            [
                'key' => '7',
                'name' => 'Item 7'
            ],
        ];
        $dataset['sessions'] = [
            [
                'datetime' => $test_date,
                'answers' => [
                    'columns' => [
                        "Order",
                        "Item",
                        "Score"
                    ],
                    "rows" => [
                        [1, "2", 7],
                        [2, "4", 6],
                        [3, "3", 5],
                        [4, "5", 4],
                        [5, "1", 3],
                        [6, "6", 2],
                        [7, "7", 1],
                    ]
                ]
            ],
            [
                'datetime' => $test_date,
                'answers' => [
                    'columns' => [
                        "Order",
                        "Item",
                        "Score"
                    ],
                    "rows" => [
                        [1, "3", 7],
                        [2, "4", 6],
                        [3, "2", 5],
                        [4, "5", 4],
                        [5, "1", 3],
                        [6, "6", 2],
                        [7, "7", 1],
                    ]
                ]
            ],
            [
                'datetime' => $test_date,
                'answers' => [
                    'columns' => [
                        "Order",
                        "Item",
                        "Score"
                    ],
                    "rows" => [
                        [1, "2", 7],
                        [2, "3", 6],
                        [3, "4", 5],
                        [4, "5", 4],
                        [5, "1", 3],
                        [6, "7", 2],
                        [7, "6", 1],
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
                [1, "2", 19],
                [2, "3", 18],
                [3, "4", 17],
                [4, "5", 12],
                [5, "1", 9],
                [6, "6", 5],
                [7, "7", 4],
            ]
        ];

        $dataset_instance = new MultipleStimulus($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }
}
