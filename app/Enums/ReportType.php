<?php

namespace App\Enums;

enum ReportType: string
{
    case PREFERENCE_ASSESSMENT_SI = "preference-assessment-si";
    case PREFERENCE_ASSESSMENT_PC = "preference-assessment-pc";
    case PREFERENCE_ASSESSMENT_MS = "preference-assessment-ms";
    case PREEFERENCE_ASSESSMENT_MSWO = "preference-assessment-mswo";
    case PREFERENCE_ASSESSMENT_MSW = "preference-assessment-msw";
    case PREFERENCE_ASSESSMENT_FOO = "preference-assessment-foo";
}
