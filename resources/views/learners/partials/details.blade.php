@php
    $backgroundClass = match ($learner->gender) {
        \App\Enums\PersonGender::FEMALE => 'bg-pink-100',
        \App\Enums\PersonGender::MALE => 'bg-sky-100',
        default => 'bg-gray-100'
    };
    $foregroundClass = match ($learner->gender) {
        \App\Enums\PersonGender::FEMALE => 'text-pink-600',
        \App\Enums\PersonGender::MALE => 'text-sky-600',
        default => 'bg-gray-100'
    };
@endphp

<div class="space-y-4">
    <div class="flex flex-col space-y-8 lg:flex-row">
        <div class="lg:w-1/4">
            <div class="{{ $backgroundClass }} flex items-start space-x-4 p-4 mr-8 rounded-lg">
                <div class="flex flex-col space-y-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-20 {{ $foregroundClass  }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <a class="border border-indigo-500 px-2 py-1 rounded-md" href="{{ route('learners.edit', ['learner' => $learner]) }}">
                        {{ __('Edit') }}
                    </a>
                </div>

                <div class="flex flex-col">
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{__('Name') }}:</span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $learner->full_name }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ __('Birth Date') }}:</span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $learner->birth_date->isoFormat('D MMMM YYYY') }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{__('Age')}}:</span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $learner->age }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div x-data="{ activeTab: 'appointments' }" class="lg:w-3/4">
                <!-- Navigation Tabs -->
                <div class="mb-4 border-b">
                    <nav class="-mb-px flex space-x-4">

                        <button type="button"
                                @click="activeTab = 'appointments'"
                                :class="activeTab === 'appointments' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap border-b-2 font-medium text-sm">
                            {{ __('Appointments Calendar') }}
                        </button>
                        <button type="button"
                                @click="activeTab = 'datasheets'"
                                :class="activeTab === 'datasheets' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap border-b-2 font-medium text-sm">
                            {{ __('Datasheets') }}
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div x-show="activeTab === 'appointments'" class="mt-4">
                    <h2 class="text-xl mb-4 font-bold">{{ __('Appointments Calendar') }}</h2>
                    @include('appointments.partials.calendar', [
                      'events' => $events,
                      'operators' => $operators,
                      'learners'  => $learners,
                      'disciplines' => $disciplines,
                      'showFilters' => false,
                    ])
                </div>
                <div x-show="activeTab === 'datasheets'" class="mt-4">
                    @include('datasheets.partials.list', ['datasheets' => $learner->datasheets, 'operators' => $operators])
                </div>
            </div>
    </div>
</div>
