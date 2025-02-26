<?php

namespace App\Services\Datasheets;

abstract class ReportAbstract
{
    abstract public function report() :array;
    abstract public function getReportTemplate(): array;
    abstract public function getDatasetTemplate(): array;
}
