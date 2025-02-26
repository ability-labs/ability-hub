<div class="space-y-4">
    <div class="flex">
        <div class="w-1/4">
            <div>
                <span class="font-bold text-gray-700 dark:text-gray-300">{{__('Firstname') }}:</span>
                <span class="text-gray-900 dark:text-gray-100">{{ $learner->first_name }}</span>
            </div>
            <div>
                <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Lastname') }}:</span>
                <span class="text-gray-900 dark:text-gray-100">{{ $learner->last_name }}</span>
            </div>
            <div>
                <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Birth Date') }}:</span>
                <span class="text-gray-900 dark:text-gray-100">{{ $learner->birth_date->isoFormat('D MMMM YYYY') }}</span>
            </div>
            <div>
                <span class="font-bold text-gray-700 dark:text-gray-300">{{__('Age')}}:</span>
                <span class="text-gray-900 dark:text-gray-100">{{ $learner->age }}</span>
            </div>



            <div class="mt-4">
                <a href="{{ route('learners.edit', $learner) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">{{__('Edit')}}</a>
            </div>
        </div>
        <div class="w-3/4">
            <div>
                @include('datasheets.partials.list', ['datasheets' => $learner->datasheets, 'operators' => $operators])
            </div>
            <div class="mt-8">
                <h2 class="text-xl mb-4 font-bold">{{ __('Appointments Calendar') }}</h2>
                @include('appointments.partials.calendar', [
                        'events' => $events,
                        'operators' => $operators,
                        'learners'  => $learners,
                        'disciplines' => $disciplines,
                        'showFilters' => false,
                    ])
            </div>
        </div>
    </div>
</div>
