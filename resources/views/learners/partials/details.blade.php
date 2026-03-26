
<div x-data="{ activeTab: 'appointments' }" class="space-y-6">
    <x-resource-header-card :resource="$learner" />

    <div class="w-full">
        <!-- Navigation Tabs -->
        <div class="mb-4 border-b">
            <nav class="-mb-px flex space-x-4">
                <button type="button" @click="activeTab = 'appointments'"
                    :class="activeTab === 'appointments' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap border-b-2 font-medium text-sm py-4">
                    {{ __('Appointments Calendar') }}
                </button>
                <button type="button" @click="activeTab = 'preferences'"
                    :class="activeTab === 'preferences' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap border-b-2 font-medium text-sm py-4">
                    {{ __('Preference Assessment') }}
                </button>
                <button type="button" @click="activeTab = 'curriculum'"
                    :class="activeTab === 'curriculum' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap border-b-2 font-medium text-sm py-4">
                    {{ __('Curriculum') }}
                </button>
                <button type="button" @click="activeTab = 'datasheets'"
                    :class="activeTab === 'datasheets' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap border-b-2 font-medium text-sm py-4">
                    {{ __('Datasheets') }}
                </button>
            </nav>
        </div>

            <!-- Tab Content -->
            <div x-show="activeTab === 'appointments'" class="mt-4 h-full">
                <h2 class="text-xl mb-4 font-bold">{{ __('Appointments Calendar') }}</h2>
                @include('appointments.partials.simple-calendar', [
                  'filterLearner' => $learner->id,
                ])
            </div>
            <div x-show="activeTab === 'preferences'">
                @include('learners.partials.preference-assessment.categories-report', ['learner' => $learner])
            </div>
            <div x-show="activeTab === 'curriculum'">
                <div class="text-center">
                    @include('learners.partials.curriculum.ebic', ['learner' => $learner])
                </div>
            </div>
            <div x-show="activeTab === 'datasheets'" class="mt-4">
                @include('datasheets.partials.list', ['datasheets' => $learner->datasheets, 'operators' => $operators])
            </div>
        </div>
    </div>
</div>

