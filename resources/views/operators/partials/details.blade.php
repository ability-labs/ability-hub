<div class="space-y-6">
    <x-resource-header-card :resource="$operator" />

    <div>
        @include('appointments.partials.simple-calendar', [
            'filterOperator' => $operator->id,
        ])
    </div>
</div>
</div>