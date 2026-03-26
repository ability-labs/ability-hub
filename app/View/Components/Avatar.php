<?php

namespace App\View\Components;

use App\Enums\PersonGender;
use App\Models\Learner;
use App\Models\Operator;
use Illuminate\View\Component;

class Avatar extends Component
{
    public string $initials;
    public string $gradient;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public $resource,
        public string $size = 'md'
    ) {
        $this->initials = $this->calculateInitials();
        $this->gradient = $this->calculateGradient();
    }

    private function calculateInitials(): string
    {
        $name = ($this->resource instanceof Operator) 
            ? $this->resource->name 
            : $this->resource->full_name;

        $parts = explode(' ', $name);
        $initials = (count($parts) >= 2)
            ? mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1)
            : mb_substr($name, 0, 1);

        return strtoupper($initials);
    }

    private function calculateGradient(): string
    {
        if ($this->resource instanceof Operator) {
            $bgColor = $this->resource->color ?? '#3b82f6';
            return "linear-gradient(135deg, $bgColor 0%, " . ($this->resource->color ? $this->resource->color . 'cc' : '#2563eb') . " 100%)";
        }

        // Learner gender colors
        $bgColor = match ($this->resource->gender ?? null) {
            PersonGender::FEMALE => '#db2777', // pink-600
            PersonGender::MALE => '#0284c7',   // sky-600
            default => '#4b5563'               // gray-600
        };

        return "linear-gradient(135deg, $bgColor 0%, " . ($bgColor . 'cc') . " 100%)";
    }

    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'xs' => 'w-8 h-8 text-[10px]',
            'sm' => 'w-10 h-10 text-xs',
            'md' => 'w-12 h-12 text-sm',
            'lg' => 'w-16 h-16 text-xl',
            'xl' => 'w-20 h-20 text-3xl',
            default => 'w-12 h-12 text-sm'
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.avatar');
    }
}
