@inject('dataService', 'App\Services\DashboardDataService')

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight flex items-center gap-3">
                <div class="p-2 bg-blue-600 rounded-lg text-white shadow-lg shadow-blue-200 dark:shadow-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </div>
                <span>{{ __('Dashboard') }}</span>
            </h2>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700">
                {{ now()->translatedFormat('l d F Y') }}
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        stats: {{ json_encode($dataService->getStats()) }},
        weeklyData: {{ json_encode($dataService->getWeeklyAppointmentStats()) }},
        disciplineDist: {{ json_encode($dataService->getDisciplineDistribution()) }}
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $statsConfig = [
                        'learners' => ['icon' => 'academic-cap', 'color' => 'blue', 'label' => __('Learners')],
                        'operators' => ['icon' => 'user-group', 'color' => 'emerald', 'label' => __('Operators')],
                        'appointments' => ['icon' => 'calendar', 'color' => 'indigo', 'label' => __('Appointments')],
                    ];
                @endphp
                
                {{-- Helper for Tailwind dynamic colors --}}
                <div class="hidden bg-blue-500 bg-emerald-500 bg-indigo-500 text-blue-600 text-emerald-600 text-indigo-600 bg-blue-100 bg-emerald-100 bg-indigo-100 bg-blue-500/10 bg-emerald-500/10 bg-indigo-500/10"></div>

                @foreach($dataService->getStats() as $key => $count)
                    @php $config = $statsConfig[$key] ?? ['icon' => 'circle', 'color' => 'gray', 'label' => ucfirst($key)]; @endphp
                    <x-dashboard.stat-card 
                        :label="$config['label']" 
                        :count="$count" 
                        :icon="$config['icon']" 
                        :color="$config['color']" 
                    />
                @endforeach
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Appointment Trends -->
                <x-dashboard.chart-card 
                    :title="__('Appointment Trends')" 
                    :subtitle="__('Last 14 days activity')" 
                    id="weeklyChart" 
                    icon-color="indigo-500"
                    :is-large="true"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-indigo-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.5 4.5L21.75 7.5M21.75 7.5V12m0-4.5H17.25" />
                    </svg>
                </x-dashboard.chart-card>

                <!-- Operator Workload -->
                <x-dashboard.chart-card 
                    :title="__('Operator Workload')" 
                    :subtitle="__('Hours this month')" 
                    id="workloadChart" 
                    icon-color="pink-500"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-pink-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </x-dashboard.chart-card>
            </div>

            <!-- Bottom Section: Lists -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Upcoming Appointments -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                             <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                            {{ __('Upcoming Appointments') }}
                        </h3>
                        <a href="{{ route('appointments.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            {{ __('View all') }}
                        </a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700 flex-1 overflow-y-auto max-h-[400px]">
                        @forelse($dataService->incomingEvents() as $event)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors flex items-center gap-4 group">
                                <div class="flex -space-x-3 transition-all duration-300 group-hover:gap-1 group-hover:space-x-0">
                                    <x-avatar :resource="$event->learner" size="sm" class="border-2 border-white dark:border-gray-800" />
                                    <x-avatar :resource="$event->operator" size="sm" class="border-2 border-white dark:border-gray-800" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <h4 class="font-bold text-sm text-gray-800 dark:text-gray-200 truncate">
                                            {{ $event->learner->full_name }}
                                        </h4>
                                        <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded-full uppercase">
                                            {{ $event->starts_at->translatedFormat('H:i') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $event->operator->name }}</span>
                                        <span class="opacity-30">•</span>
                                        <span>{{ $event->starts_at->translatedFormat('d M') }}</span>
                                    </p>
                                </div>
                            </div>
                        @empty
                             <div class="p-12 text-center text-gray-400 italic">
                                {{ __('No upcoming appointments') }}
                             </div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                            {{ __('Recent Activity') }}
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700 flex-1 overflow-y-auto max-h-[400px]">
                        @foreach($dataService->getRecentActivity() as $activity)
                            <div class="p-4 flex items-center gap-4">
                                <div class="size-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    @if($activity['type'] === 'learner')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 text-blue-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 text-emerald-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                        {{ $activity['name'] }}
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        {{ __('New') }} {{ __($activity['type']) }} • {{ \Carbon\Carbon::parse($activity['date'])->diffForHumans() }}
                                    </p>
                                </div>
                                <a href="{{ $activity['type'] === 'learner' ? route('learners.show', $activity['resource']) : route('operators.show', $activity['resource']) }}" 
                                   class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4 text-gray-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
            const textColor = isDark ? '#9ca3af' : '#6b7280';

            // Weekly Chart
            const weeklyData = @json($dataService->getWeeklyAppointmentStats());
            console.log('Weekly Data:', weeklyData);
            const labels = Object.keys(weeklyData).map(date => {
                const d = new Date(date);
                return d.toLocaleDateString('{{ app()->getLocale() }}', { day: 'numeric', month: 'short' });
            });
            const values = Object.values(weeklyData);

            new Chart(document.getElementById('weeklyChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '{{ __("Appointments") }}',
                        data: values,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#3b82f6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: { color: textColor, precision: 0 }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor }
                        }
                    }
                }
            });

            const workloadData = @json($dataService->getOperatorWorkload());
            const workLabels = workloadData.map(d => d.name);
            const workValues = workloadData.map(d => d.hours);
            const workColors = workloadData.map(d => d.color);
            
            if (workloadData.length > 0) {
                new Chart(document.getElementById('workloadChart'), {
                    type: 'doughnut',
                    data: {
                        labels: workLabels,
                        datasets: [{
                            data: workValues,
                            backgroundColor: workColors,
                            borderWidth: 0,
                            hoverOffset: 15
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    color: textColor,
                                    font: { weight: '600' },
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => ` ${context.label}: ${context.raw}h`
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
