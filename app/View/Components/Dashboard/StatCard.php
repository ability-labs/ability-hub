<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class StatCard extends Component
{
    public function __construct(
        public string $label,
        public string $count,
        public string $icon,
        public string $color = 'blue'
    ) {}

    public function render()
    {
        return view('components.dashboard.stat-card', [
            'label' => $this->label,
            'count' => $this->count,
            'icon' => $this->icon,
            'color' => $this->color,
        ]);
    }
}
