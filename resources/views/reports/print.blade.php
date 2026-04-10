@php
    $isLearner = $tab === 'learners';
    $monthName = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');
    $totalHours = $items->sum('total_hours');
    $showSummary = $showSummary ?? true;
    $isShared = $isShared ?? false;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Monthly Report') }} — {{ $monthName }}</title>
    @if(!$isShared)
        @routes
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #1a1a1a; font-size: 12px; line-height: 1.4; background: #f1f5f9; }

        .page { page-break-after: always; padding: 24px; display: flex; flex-direction: column; }
        .page:last-child { page-break-after: auto; }

        .page-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #1a1a1a; padding-bottom: 8px; margin-bottom: 16px; }
        .page-header h1 { font-size: 14px; font-weight: 900; }
        .page-header .meta { font-size: 10px; color: #666; text-align: right; }

        .entity-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #ccc; }
        .entity-name { font-size: 18px; font-weight: 900; }
        .entity-detail { font-size: 11px; color: #666; margin-top: 2px; }
        .entity-hours { font-size: 28px; font-weight: 900; }
        .entity-hours small { font-size: 14px; font-weight: 600; color: #999; }
        .badges { display: flex; gap: 4px; margin-top: 4px; flex-wrap: wrap; }
        .badge { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 2px 6px; border: 1px solid #ccc; border-radius: 4px; color: #555; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; flex: 1; }
        th { text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #999; padding: 4px 8px 4px 0; border-bottom: 1px solid #ddd; }
        th:last-child { text-align: right; padding-right: 0; }
        td { padding: 5px 8px 5px 0; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
        td:last-child { text-align: right; padding-right: 0; font-weight: 700; white-space: nowrap; }

        .summary-table th { border-bottom: 2px solid #999; padding: 6px 8px 6px 0; }
        .summary-table td { padding: 6px 8px 6px 0; border-bottom: 1px solid #e0e0e0; }
        .summary-table tr:last-child td { border-bottom: 2px solid #999; }
        .summary-total { display: flex; justify-content: flex-end; margin-top: 8px; font-size: 14px; font-weight: 900; }

        /* Toolbar */
        .toolbar { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 12px; background: #fff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 10; flex-wrap: wrap; }
        .toolbar button, .toolbar a { display: inline-flex; align-items: center; gap: 5px; padding: 8px 14px; border: none; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.15s; text-decoration: none; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #e2e8f0; color: #475569; }
        .btn-secondary:hover { background: #cbd5e1; }

        /* Toast */
        .toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #1e293b; color: #fff; padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 600; z-index: 100; opacity: 0; transition: opacity 0.2s; pointer-events: none; }
        .toast.show { opacity: 1; }

        @media print {
            .toolbar, .toast { display: none !important; }
            .page { padding: 0; min-height: auto; }
            body { font-size: 11px; background: #fff; }
        }

        @media screen {
            .page { max-width: 800px; margin: 12px auto; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 4px; }
        }

        @media screen and (max-width: 640px) {
            .page { margin: 8px; padding: 16px; }
            .entity-name { font-size: 15px; }
            .entity-hours { font-size: 22px; }
            .entity-hours small { font-size: 12px; }
            .toolbar button, .toolbar a { padding: 7px 10px; font-size: 11px; }
        }
    </style>
</head>
<body>
    {{-- Toolbar --}}
    <div class="toolbar">
        <button class="btn-primary" onclick="window.print()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m0 0a48.09 48.09 0 0 1 18.5 0M6.75 6V3.75m10.5 0V6"/></svg>
            {{ __('Print') }}
        </button>
        <a class="btn-secondary" href="{{ route($isShared ? 'reports.shared' : 'reports.download', request()->query()) }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12M12 16.5V3"/></svg>
            {{ __('Download PDF') }}
        </a>
        @if(!$isShared)
            <button class="btn-secondary" onclick="shareReport()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"/></svg>
                {{ __('Share') }}
            </button>
        @endif
    </div>

    <div class="toast" id="toast"></div>

    {{-- Report content --}}
    <div id="report-content">
        @if($showSummary)
        <div class="page">
            <div class="page-header">
                <div>
                    <h1>{{ __('Monthly Report') }}</h1>
                    <div class="meta">{{ $isLearner ? __('Learners') : __('Operators') }}</div>
                </div>
                <div class="meta">{{ $monthName }}</div>
            </div>

            <table class="summary-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name') }}</th>
                        @if($isLearner)
                            <th>{{ __('Age') }}</th>
                        @endif
                        <th>{{ __('Disciplines') }}</th>
                        <th style="text-align: right">{{ __('Hours') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $idx => $item)
                        @php
                            $resource = $item['resource'];
                            $name = $isLearner ? $resource->full_name : $resource->name;
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td style="font-weight: 700">{{ $name }}</td>
                            @if($isLearner)
                                <td>{{ $resource->age }}</td>
                            @endif
                            <td>{{ $item['disciplines']->join(', ') }}</td>
                            <td>{{ number_format($item['total_hours'], 1) }}h</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="summary-total">
                {{ __('Total Hours') }}: {{ number_format($totalHours, 1) }}h
            </div>
        </div>
        @endif

        @foreach($items as $item)
            @php
                $resource = $item['resource'];
                $name = $isLearner ? $resource->full_name : $resource->name;
            @endphp
            <div class="page">
                <div class="page-header">
                    <div>
                        <h1>{{ __('Monthly Report') }}</h1>
                        <div class="meta">{{ $isLearner ? __('Learners') : __('Operators') }}</div>
                    </div>
                    <div class="meta">{{ $monthName }}</div>
                </div>

                <div class="entity-header">
                    <div>
                        <div class="entity-name">{{ $name }}</div>
                        @if($isLearner)
                            <div class="entity-detail">{{ $resource->age }}</div>
                        @endif
                        <div class="badges">
                            @foreach($item['disciplines'] as $disc)
                                <span class="badge">{{ $disc }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <div class="entity-hours">{{ number_format($item['total_hours'], 1) }}<small>h</small></div>
                    </div>
                </div>

                <table>
                    <thead>
                        @if($isLearner)
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Time') }}</th>
                                <th>{{ __('Operator') }}</th>
                                <th style="text-align: right">{{ __('Hours') }}</th>
                            </tr>
                        @else
                            <tr>
                                <th>{{ __('Learner') }}</th>
                                <th style="text-align: right">{{ __('Hours') }}</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @foreach($item['breakdown'] as $detail)
                            @if($isLearner)
                                <tr>
                                    <td>{{ $detail['date'] }}</td>
                                    <td>{{ $detail['time'] }}</td>
                                    <td>{{ $detail['operator_name'] }}</td>
                                    <td>{{ number_format($detail['hours'], 1) }}h</td>
                                </tr>
                            @else
                                <tr>
                                    <td>{{ $detail['name'] }}</td>
                                    <td>{{ number_format($detail['hours'], 1) }}h</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    @if(!$isShared)
    <script>
        function showToast(msg) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2500);
        }

        async function shareReport() {
            try {
                const params = new URLSearchParams(window.location.search);
                const resp = await fetch(route('reports.share-link') + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await resp.json();

                if (navigator.share) {
                    await navigator.share({
                        title: '{{ __("Monthly Report") }} — {{ $monthName }}',
                        url: data.url
                    });
                } else {
                    await navigator.clipboard.writeText(data.url);
                    showToast('{{ __("Link copied to clipboard") }}');
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error('Share failed:', err);
                    showToast('{{ __("Could not share report") }}');
                }
            }
        }
    </script>
    @endif
</body>
</html>
