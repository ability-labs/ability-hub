@inject('preferences', 'App\Services\PreferenceAssessmentService')
@php
    $data = $preferences->reportCategoryPreferences($learner)
@endphp
<div class="container mx-auto">
    <!-- Grafico Generale -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-center">{{ __($data['general']['label']) }}</h2>
        <canvas id="generalChart" class="mx-auto"></canvas>
    </div>

    <!-- Grafici per Categoria in due colonne -->
    <div class="grid grid-cols-5">
        @foreach($data as $key => $graph)
            @if($key !== 'general' && count($graph['data']['datasets']) > 0)
                <div class="border rounded p-4">
                    <h3 class="text-sm font-bold text-center mb-4">{{ $graph['label'] }}</h3>
                    <canvas id="chart-{{ $key }}"></canvas>
                </div>
            @endif
        @endforeach
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
    <script>
        // Grafico generale
        const generalCtx = document.getElementById('generalChart').getContext('2d');
        const generalChart = new Chart(generalCtx, {
            type: 'radar',
            data: @json($data['general']['data']),
            options: {
                scales: {
                    r: {
                        angleLines: {
                            display: false
                        },
                        suggestedMin: 0,
                    }
                },
                datasets: {
                    fill: true,
                    snapGaps: true
                },
                elements: {
                    line: { borderWidth: 3 }
                },
                plugins: {
                    legend: {
                        display: false,
                    }
                }
            }
        });

        // Grafici per ciascuna categoria (escludendo "general")
        @foreach($data as $key => $graph)
        @if($key !== 'general' && count($graph['data']['datasets']) > 0)
        const ctx_{{ $key }} = document.getElementById('chart-{{ $key }}').getContext('2d');
        const chart_{{ $key }} = new Chart(ctx_{{ $key }}, {
{{--            type: '{{count($graph['data']['datasets']) > 2 ? 'radar' : 'polarArea' }}',--}}
            type: 'polarArea',
            data: @json($graph['data']),
            options: {
                // scales: {
                //     r: {
                //         angleLines: {
                //             display: false
                //         },
                //         suggestedMin: 0,
                //     }
                // },
                datasets: {
                    fill: true,
                    snapGaps: true
                },
                elements: {
                    line: { borderWidth: 3 }
                },
                plugins: {
                    legend: {
                        display: false,
                    }
                }
            }
        });
        @endif
        @endforeach
    </script>
@endpush
