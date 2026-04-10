@php
    $totalHours = $items->sum('total_hours');
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #222;
            font-size: 9pt;
            line-height: 1.4;
        }
    </style>
    <style>
        @page {
            margin: 45pt 40pt;
        }

        /* ── Page header ── */
        .page-header {
            border-bottom: 1.5pt solid #333;
            padding-bottom: 4pt;
            margin-bottom: 10pt;
        }
        .page-header h1 {
            font-size: 10pt;
            font-weight: 900;
            display: inline;
        }
        .page-header .meta-right {
            float: right;
            font-size: 8pt;
            font-weight: 700;
            color: #666;
        }
        .page-header .subtitle {
            font-size: 7pt;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            font-weight: 700;
        }

        /* ── Entity header ── */
        .entity-header {
            background-color: #f5f5f5;
            border: 0.75pt solid #ddd;
            border-radius: 3pt;
            padding: 7pt 10pt;
            margin-bottom: 8pt;
        }
        .entity-name {
            font-size: 11pt;
            font-weight: 900;
            color: #111;
        }
        .entity-detail {
            font-size: 7.5pt;
            color: #777;
        }
        .entity-hours {
            float: right;
            font-size: 16pt;
            font-weight: 900;
            color: #111;
            margin-top: -4pt;
        }
        .entity-hours small {
            font-size: 9pt;
            font-weight: 600;
            color: #999;
        }
        .badge {
            display: inline-block;
            font-size: 6.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
            padding: 1pt 5pt;
            border: 0.5pt solid #bbb;
            border-radius: 3pt;
            color: #555;
            margin-right: 3pt;
            margin-top: 4pt;
        }

        /* ── Tables ── */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            font-size: 6.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4pt;
            color: #888;
            padding: 4pt 6pt 4pt 0;
            border-bottom: 1pt solid #bbb;
        }
        th.right {
            text-align: right;
            padding-right: 0;
        }
        td {
            padding: 3pt 6pt 3pt 0;
            border-bottom: 0.5pt solid #eee;
            font-size: 8.5pt;
            color: #333;
        }
        td.right {
            text-align: right;
            padding-right: 0;
            font-weight: 700;
            color: #222;
        }
        td.bold {
            font-weight: 700;
            color: #111;
        }
        tr:last-child td {
            border-bottom: none;
        }

        /* ── Summary-specific ── */
        .summary-table th {
            border-bottom: 1.5pt solid #888;
            padding: 4pt 6pt 4pt 0;
        }
        .summary-table td {
            padding: 4pt 6pt 4pt 0;
            border-bottom: 0.5pt solid #ddd;
        }
        .summary-table tr:last-child td {
            border-bottom: 1.5pt solid #888;
        }

        .total-row {
            text-align: right;
            font-size: 9pt;
            font-weight: 900;
            margin-top: 6pt;
            padding-top: 4pt;
            border-top: 1pt solid #999;
            color: #111;
        }

        /* ── Footer ── */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #aaa;
            padding-top: 6pt;
            border-top: 0.5pt solid #ddd;
        }

        .page-break {
            page-break-after: always;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    @if($showSummary)
    {{-- ═══ Summary page ═══ --}}
    <div class="{{ $items->count() > 0 ? 'page-break' : '' }}">
        <div class="page-header clearfix">
            <div class="meta-right">{{ $monthName }}</div>
            <h1>{{ __('Monthly Report') }}</h1>
            <div class="subtitle">{{ $isLearner ? __('Learners') : __('Operators') }}</div>
        </div>

        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 22pt">#</th>
                    <th>{{ __('Name') }}</th>
                    @if($isLearner)
                        <th style="width: 60pt">{{ __('Age') }}</th>
                    @endif
                    <th>{{ __('Disciplines') }}</th>
                    <th class="right" style="width: 50pt">{{ __('Hours') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $item)
                    <tr>
                        <td style="color: #999">{{ $idx + 1 }}</td>
                        <td class="bold">{{ $isLearner ? $item['resource']->full_name : $item['resource']->name }}</td>
                        @if($isLearner)
                            <td>{{ $item['resource']->age }}</td>
                        @endif
                        <td>{{ $item['disciplines']->join(', ') }}</td>
                        <td class="right">{{ number_format($item['total_hours'], 1) }}h</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="total-row">{{ __('Total Hours') }}: {{ number_format($totalHours, 1) }}h</div>
    </div>
    @endif

    {{-- ═══ Individual pages ═══ --}}
    @foreach($items as $itemIdx => $item)
        @php
            $resource = $item['resource'];
            $name = $isLearner ? $resource->full_name : $resource->name;
            $isLast = $itemIdx === $items->count() - 1;
        @endphp
        <div class="{{ $isLast ? '' : 'page-break' }}">
            <div class="page-header clearfix">
                <div class="meta-right">{{ $monthName }}</div>
                <h1>{{ __('Monthly Report') }}</h1>
                <div class="subtitle">{{ $isLearner ? __('Learners') : __('Operators') }}</div>
            </div>

            <div class="entity-header clearfix">
                <div class="entity-hours">{{ number_format($item['total_hours'], 1) }}<small>h</small></div>
                <div class="entity-name">{{ $name }}</div>
                @if($isLearner)
                    <div class="entity-detail">{{ $resource->age }}</div>
                @endif
                <div>
                    @foreach($item['disciplines'] as $disc)
                        <span class="badge">{{ $disc }}</span>
                    @endforeach
                </div>
            </div>

            <table>
                <thead>
                    @if($isLearner)
                        <tr>
                            <th style="width: 55pt">{{ __('Date') }}</th>
                            <th style="width: 75pt">{{ __('Time') }}</th>
                            <th>{{ __('Operator') }}</th>
                            <th class="right" style="width: 45pt">{{ __('Hours') }}</th>
                        </tr>
                    @else
                        <tr>
                            <th>{{ __('Learner') }}</th>
                            <th class="right" style="width: 50pt">{{ __('Hours') }}</th>
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
                                <td class="right">{{ number_format($detail['hours'], 1) }}h</td>
                            </tr>
                        @else
                            <tr>
                                <td>{{ $detail['name'] }}</td>
                                <td class="right">{{ number_format($detail['hours'], 1) }}h</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
