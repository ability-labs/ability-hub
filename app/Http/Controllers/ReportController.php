<?php

namespace App\Http\Controllers;

use App\Services\ReportSummaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ReportController extends Controller
{
    public function index(Request $request, ReportSummaryService $service): mixed
    {
        $validated = $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2100',
            'tab' => 'nullable|in:operators,learners',
        ]);

        $month = (int) ($validated['month'] ?? now()->month);
        $year = (int) ($validated['year'] ?? now()->year);
        $tab = $validated['tab'] ?? 'learners';

        if ($request->ajax()) {
            $items = ($tab === 'learners')
                ? $service->getLearnerSummary($month, $year)
                : $service->getOperatorSummary($month, $year);

            return view('reports.partials.cards', [
                'items' => $items,
                'tab' => $tab,
            ])->render();
        }

        return view('reports.index', [
            'initialMonth' => $month,
            'initialYear' => $year,
        ]);
    }

    public function print(Request $request, ReportSummaryService $service): mixed
    {
        $params = $this->validatePrintParams($request);
        $items = $this->getFilteredItems($service, $params);

        return view('reports.print', [
            'items' => $items,
            'tab' => $params['tab'],
            'month' => $params['month'],
            'year' => $params['year'],
            'showSummary' => $params['summary'],
            'isShared' => false,
        ]);
    }

    public function download(Request $request, ReportSummaryService $service): mixed
    {
        $params = $this->validatePrintParams($request);
        $items = $this->getFilteredItems($service, $params);
        $isLearner = $params['tab'] === 'learners';
        $monthName = \Carbon\Carbon::create($params['year'], $params['month'], 1)->translatedFormat('F Y');

        $pdf = Pdf::loadView('reports.pdf', [
            'items' => $items,
            'tab' => $params['tab'],
            'month' => $params['month'],
            'year' => $params['year'],
            'showSummary' => $params['summary'],
            'isLearner' => $isLearner,
            'monthName' => $monthName,
        ]);

        $pdf->getDomPDF()->setPaper('a4', 'portrait');
        $pdf->getDomPDF()->getOptions()->set('defaultFont', 'DejaVu Sans');

        $filename = __('Monthly Report').' - '.$monthName.'.pdf';

        return $pdf->download($filename);
    }

    public function shared(Request $request, ReportSummaryService $service): mixed
    {
        $params = $this->validatePrintParams($request);
        $items = $this->getFilteredItems($service, $params);

        return view('reports.print', [
            'items' => $items,
            'tab' => $params['tab'],
            'month' => $params['month'],
            'year' => $params['year'],
            'showSummary' => $params['summary'],
            'isShared' => true,
        ]);
    }

    public function shareLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'tab' => 'required|in:operators,learners',
            'ids' => 'nullable|array',
            'ids.*' => 'string',
            'summary' => 'nullable|boolean',
        ]);

        $params = [
            'month' => $validated['month'],
            'year' => $validated['year'],
            'tab' => $validated['tab'],
        ];

        if (! empty($validated['ids'])) {
            $params['ids'] = $validated['ids'];
        }

        if (isset($validated['summary'])) {
            $params['summary'] = $validated['summary'] ? '1' : '0';
        }

        $url = URL::temporarySignedRoute('reports.shared', now()->addDays(30), $params);

        return response()->json(['url' => $url]);
    }

    /**
     * @return array{month: int, year: int, tab: string, ids: ?array, summary: bool}
     */
    private function validatePrintParams(Request $request): array
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'tab' => 'required|in:operators,learners',
            'ids' => 'nullable|array',
            'ids.*' => 'string',
            'summary' => 'nullable',
        ]);

        return [
            'month' => (int) $validated['month'],
            'year' => (int) $validated['year'],
            'tab' => $validated['tab'],
            'ids' => $validated['ids'] ?? null,
            'summary' => ($validated['summary'] ?? '1') !== '0',
        ];
    }

    private function getFilteredItems(ReportSummaryService $service, array $params): mixed
    {
        $items = ($params['tab'] === 'learners')
            ? $service->getLearnerSummary($params['month'], $params['year'])
            : $service->getOperatorSummary($params['month'], $params['year']);

        if ($params['ids']) {
            $items = $items->filter(fn ($item) => in_array($item['resource']->id, $params['ids']))->values();
        }

        return $items;
    }
}
