<?php

namespace App\View\Components;

use App\Models\Learner;
use App\Models\Operator;
use Illuminate\View\Component;

class ResourceListCard extends Component
{
    public string $name;
    public bool $isOperator;
    public bool $isLearner;
    public string $showRoute;
    public string $editRoute;
    public string $genderColor = 'gray';
    public array $stats = [];
    public ?string $badgeLabel = null;
    public array $badges = [];

    /**
     * Create a new component instance.
     */
    public function __construct(public $resource)
    {
        $this->isOperator = $resource instanceof Operator;
        $this->isLearner = $resource instanceof Learner;
        $this->name = $this->isOperator ? $resource->name : $resource->full_name;

        $this->calculateRoutes();
        $this->calculateData();
    }

    private function calculateRoutes(): void
    {
        if ($this->isOperator) {
            $this->showRoute = route('operators.show', $this->resource);
            $this->editRoute = route('operators.edit', $this->resource);
        } else {
            $this->showRoute = route('learners.show', $this->resource);
            $this->editRoute = route('learners.edit', $this->resource);
        }
    }

    private function calculateData(): void
    {
        if ($this->isLearner) {
            $this->genderColor = match ($this->resource->gender ?? null) {
                \App\Enums\PersonGender::FEMALE => 'rose',
                \App\Enums\PersonGender::MALE => 'sky',
                default => 'gray'
            };

            $this->stats[] = ['label' => __('Age'), 'value' => $this->resource->age];
            $this->stats[] = ['label' => __('Weekly Hours'), 'value' => $this->resource->weekly_hours . 'h'];
        } else {
            $this->stats[] = ['label' => __('Slots'), 'value' => $this->resource->slots->count(), 'icon' => true];
            
            $this->badgeLabel = __('Disciplines');
            foreach ($this->resource->disciplines as $d) {
                $this->badges[] = __($d->getTranslation('name', app()->getLocale()));
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.resource-list-card', [
            'name' => $this->name,
            'isOperator' => $this->isOperator,
            'isLearner' => $this->isLearner,
            'showRoute' => $this->showRoute,
            'editRoute' => $this->editRoute,
            'genderColor' => $this->genderColor,
            'stats' => $this->stats,
            'badgeLabel' => $this->badgeLabel,
            'badges' => $this->badges,
        ]);
    }
}