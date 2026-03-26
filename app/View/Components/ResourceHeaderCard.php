<?php

namespace App\View\Components;

use App\Models\Learner;
use App\Models\Operator;
use Illuminate\View\Component;

class ResourceHeaderCard extends Component
{
    public string $name;
    public bool $isOperator;
    public bool $isLearner;
    public array $details = [];
    public array $badges = [];
    public string $badgeLabel;
    public string $editRoute;
    public string $backRoute;

    /**
     * Create a new component instance.
     */
    public function __construct(public $resource)
    {
        $this->isOperator = $resource instanceof Operator;
        $this->isLearner = $resource instanceof Learner;
        $this->name = $this->isOperator ? $resource->name : $resource->full_name;

        $this->calculateDetails();
        $this->calculateBadges();
        $this->calculateRoutes();
    }

    private function calculateDetails(): void
    {
        if ($this->isOperator) {
            $this->details[] = ['label' => __('VAT'), 'value' => $this->resource->vat_id ?? __('VAT Not Found'), 'icon' => 'card'];
            $this->details[] = ['label' => __('Joined'), 'value' => $this->resource->created_at->format('d/m/Y'), 'icon' => 'calendar'];
        } else {
            $this->details[] = ['label' => __('Age'), 'value' => $this->resource->age . ' ', 'icon' => 'user'];
            $this->details[] = ['label' => __('Birth Date'), 'value' => $this->resource->birth_date?->format('d/m/Y') ?? '-', 'icon' => 'calendar'];
        }
    }

    private function calculateBadges(): void
    {
        $this->badgeLabel = $this->isOperator ? __('Disciplines') : __('Assigned operators');
        
        if ($this->isOperator) {
            foreach ($this->resource->disciplines as $d) {
                $this->badges[] = ['text' => __($d->getTranslation('name', app()->getLocale())), 'color' => 'gray'];
            }
        } else {
            foreach ($this->resource->operators as $o) {
                $this->badges[] = ['text' => $o->name, 'color' => 'blue'];
            }
        }
    }

    private function calculateRoutes(): void
    {
        $this->editRoute = $this->isOperator ? route('operators.edit', $this->resource) : route('learners.edit', $this->resource);
        $this->backRoute = $this->isOperator ? route('operators.index') : route('learners.index');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.resource-header-card', [
            'name' => $this->name,
            'details' => $this->details,
            'badges' => $this->badges,
            'badgeLabel' => $this->badgeLabel,
            'editRoute' => $this->editRoute,
            'backRoute' => $this->backRoute,
        ]);
    }
}
