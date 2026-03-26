<div class="space-y-6">

    <div>
        @include('appointments.partials.simple-calendar', [
            'filterOperator' => $operator->id,
        ])
    </div>
</div>