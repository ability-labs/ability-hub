<?php

namespace App\Services\Datasheets;

abstract class ReportAbstract
{
    abstract public function report() :array;
    abstract public function getReportTemplate(): array;
    abstract public function getDatasetTemplate(): array;

    abstract public function hasItems(): bool;
    abstract public function hasSessions(): bool;
    abstract public function hasLegend(): bool;
    abstract public function getLegend(): ?array;
    abstract public function mockData(): array;
    abstract protected function mockSessionData(array $items, int $sessionIndex): array;
}
