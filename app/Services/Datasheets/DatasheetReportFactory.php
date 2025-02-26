<?php

namespace App\Services\Datasheets;

use App\Enums\ReportType;
use App\Models\Datasheet;
use App\Models\DatasheetType;
use App\Services\Datasheets\PreferenceAssessment\FreeOperantObservation;
use App\Services\Datasheets\PreferenceAssessment\MultipleStimulus;
use App\Services\Datasheets\PreferenceAssessment\MultipleStimulusWithoutReplacement;
use App\Services\Datasheets\PreferenceAssessment\MultipleStimulusWithReplacement;
use App\Services\Datasheets\PreferenceAssessment\PairedChoice;
use App\Services\Datasheets\PreferenceAssessment\PreferenceAssessmentAbstract;
use App\Services\Datasheets\PreferenceAssessment\SingleItem;

class DatasheetReportFactory
{
    const STRATEGY_MAP = [
        ReportType::PREFERENCE_ASSESSMENT_SI->value => SingleItem::class,
        ReportType::PREFERENCE_ASSESSMENT_PC->value => PairedChoice::class,
        ReportType::PREFERENCE_ASSESSMENT_MS->value => MultipleStimulus::class,
        ReportType::PREEFERENCE_ASSESSMENT_MSWO->value => MultipleStimulusWithoutReplacement::class,
        ReportType::PREFERENCE_ASSESSMENT_MSW->value => MultipleStimulusWithReplacement::class,
        ReportType::PREFERENCE_ASSESSMENT_FOO->value => FreeOperantObservation::class,
    ];

    static function fromDatasheet(Datasheet $datasheet): ReportAbstract
    {
        $type = $datasheet->type;
        if (!array_key_exists($type->id, self::STRATEGY_MAP))
            throw new \InvalidArgumentException('No buildable type found');

        $class = self::STRATEGY_MAP[$type->id];

        return new $class($datasheet->data->toArray());
    }
}
