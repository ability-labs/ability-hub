<?php

namespace Tests\Feature\Datasheets;

use App\Services\Datasets\PreferenceAssessment\SingleItem;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SingleItemPreferenceAssessmentTest extends TestCase
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
        $dataset_class = new SingleItem([]);
        $this->assertEquals($expected_result, $dataset_class->getReportTemplate());
    }
    public function test_it_will_report_single_item_preference_assessment_results_with_empty_dataset()
    {
        $dataset = self::EMPTY_DATASET;
        $expected_result = self::EXPECTED_TEMPLATE;

        $dataset_instance = new SingleItem($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }
    public function test_it_will_report_single_item_preference_assessment_results_with_empty_sessions_dataset()
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
    public function test_it_will_report_single_item_preference_assessment_results_with_empty_items_dataset()
    {
        $test_date = Carbon::parse($this->faker->dateTimeThisDecade());
        $dataset = self::EMPTY_DATASET;
        $dataset['sessions'] = [
            [
                'datetime' => $test_date,
                'answers' => [
                    'columns' => [
                        "Item",
                        "Answer"
                    ],
                    "rows" => [
                        ["A", "A"],
                        ["B", "I"],
                        ["C", "I"],
                        ["D", "NA"]
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
                        ["A", "I"],
                        ["B", "I"],
                        ["C", "I"],
                        ["D", "NA"]
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
                        ["A", "I"],
                        ["B", "I"],
                        ["C", "A"],
                        ["D", "A"]
                    ]
                ]
            ],
        ];
        $expected_result = self::EXPECTED_TEMPLATE;

        $dataset_instance = new SingleItem($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }

    public function test_it_will_report_single_item_preference_assessment_results_with_partial_dataset()
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
                        ["A", "A"],
                        ["B", "I"],
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
                [1, "B", 1],
                [2, "C", 0],
                [2, "D", 0],
                [4, "A", -1]
            ]
        ];

        $dataset_instance = new SingleItem($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }
    public function test_it_will_report_single_item_preference_assessment_results_with_complete_dataset()
    {
        $test_date = Carbon::parse($this->faker->dateTimeThisDecade());
        $dataset = [
            'items' => [
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
            ],
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
                [
                    'datetime' => $test_date,
                    'answers' => [
                        'columns' => [
                            "Item",
                            "Answer"
                        ],
                        "rows" => [
                            ["A", "A"],
                            ["B", "I"],
                            ["C", "I"],
                            ["D", "NA"]
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
                            ["A", "I"],
                            ["B", "I"],
                            ["C", "I"],
                            ["D", "NA"]
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
                            ["A", "I"],
                            ["B", "I"],
                            ["C", "A"],
                            ["D", "A"]
                        ]
                    ]
                ],
            ]
        ];
        $expected_result = [
            'columns' => [
                'Order',
                "Item",
                "Points"
            ],
            "rows" => [
                [1, "B", 3],
                [2, "A", 1],
                [2, "C", 1],
                [4, "D", -1]
            ]
        ];

        $dataset_instance = new SingleItem($dataset);
        $report = $dataset_instance->report();

        $this->assertEquals($expected_result, $report);
    }
}
