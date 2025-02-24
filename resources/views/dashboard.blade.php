@inject('dataService', 'App\Services\DashboardDataService')

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Report delle risorse -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @foreach($dataService->getStats() as $resource => $count)
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6 flex items-center">
                        <div class="flex-shrink-0 text-indigo-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                            </svg>

                        </div>
                        <div class="ml-4">
                            <div class="text-gray-500 text-sm">
                                {{ __(ucfirst($resource)) }}
                            </div>
                            <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">
                                {{ $count }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Prossimi Appuntamenti -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>

                        <span>{{ __('Upcoming Appointments') }}</span>
                    </h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                        @forelse($dataService->incomingEvents() as $event)
                            <li class="py-2">
                                <div class="flex justify-between items-center">
                  <span class="font-semibold text-gray-700 dark:text-gray-200">
                    {{ $event->title }}
                  </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ \Carbon\Carbon::parse($event->starts_at)->format('d/m/Y H:i') }}
                  </span>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $event->operator->name }} | {{ $event->learner->full_name }}
                                </div>
                            </li>
                        @empty
                            <x-empty-state />
                        @endforelse
                    </ul>
                </div>
                <!-- Discipline trattate dai propri operatori -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>

                        <span>
                            {{ __('Disciplines Handled') }}
                        </span>
                    </h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                        @forelse($dataService->getOperatorDisciplines() as $discipline)
                            <li class="py-2 flex items-center">
                                <span class="inline-block w-4 h-4 mr-2 rounded" style="background-color: {{ $discipline->color }}"></span>
                                <span class="text-gray-700 dark:text-gray-200">
                                  {{ __($discipline->getTranslation('name', app()->getLocale())) }}
                                </span>
                            </li>
                        @empty
                            <x-empty-state />
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
