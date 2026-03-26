<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class ChartCard extends Component
{
    public function __construct(
        public string $title,
        public string $subtitle,
        public string $id,
        public string $iconColor = 'indigo-500',
        public bool $isLarge = false
    ) {}

    public function render()
    {
        return view('components.dashboard.chart-card', [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'id' => $this->id,
            'iconColor' => $this->iconColor,
            'isLarge' => $this->isLarge,
        ]);
    }
}
