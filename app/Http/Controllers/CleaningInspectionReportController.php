<?php

namespace App\Http\Controllers;

use App\Models\CleaningInspectionReport;
use App\Models\CleaningInspectionReportItem;
use App\Models\InspectionHead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CleaningInspectionReportController extends Controller
{
    public function index(Request $request)
    {
        $query = CleaningInspectionReport::with('reporter')
            ->orderByDesc('report_date');

        if ($request->filled('date_from')) {
            $query->where('report_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('report_date', '<=', $request->date_to);
        }

        $reports = $query->paginate(20)->withQueryString();

        return view('cleaning_inspection_reports.index', compact('reports'));
    }

    public function create()
    {
        $heads = InspectionHead::active()->cleaning()->orderBy('sort_order')->orderBy('name')->get();
        $today = now()->toDateString();

        // Check if today's report already exists
        $existing = CleaningInspectionReport::where('report_date', $today)->first();
        if ($existing) {
            return redirect()->route('cleaning-inspections.edit', $existing)
                ->with('info', "Today's report already exists. Edit it below.");
        }

        return view('cleaning_inspection_reports.create', compact('heads', 'today') + ['report' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'report_date'            => 'required|date|unique:cleaning_inspection_reports,report_date',
            'overall_remarks'        => 'nullable|string',
            'items'                  => 'nullable|array',
            'items.*.status'         => 'nullable|in:yes,no,na',
            'items.*.remarks'        => 'nullable|string|max:1000',
            'items.*.image'          => 'nullable|image|max:200', // 200 KB
        ]);

        $report = CleaningInspectionReport::create([
            'report_date'     => $request->report_date,
            'reported_by'     => Auth::id(),
            'overall_remarks' => $request->overall_remarks,
        ]);

        $this->saveItems($report, $request);

        return redirect()->route('cleaning-inspections.index')
            ->with('success', 'Cleaning inspection report saved successfully.');
    }

    public function edit(CleaningInspectionReport $cleaningInspection)
    {
        $report = $cleaningInspection->load('items');
        $heads  = InspectionHead::active()->cleaning()->orderBy('sort_order')->orderBy('name')->get();
        $existingItems = $report->items->keyBy('inspection_head_id');

        return view('cleaning_inspection_reports.edit', compact('report', 'heads', 'existingItems'));
    }

    public function update(Request $request, CleaningInspectionReport $cleaningInspection)
    {
        $request->validate([
            'overall_remarks' => 'nullable|string',
            'items'           => 'nullable|array',
            'items.*.status'  => 'nullable|in:yes,no,na',
            'items.*.remarks' => 'nullable|string|max:1000',
            'items.*.image'   => 'nullable|image|max:200',
        ]);

        $cleaningInspection->update([
            'overall_remarks' => $request->overall_remarks,
        ]);

        $this->saveItems($cleaningInspection, $request);

        return redirect()->route('cleaning-inspections.index')
            ->with('success', 'Cleaning inspection report updated.');
    }

    public function show(CleaningInspectionReport $cleaningInspection)
    {
        $report = $cleaningInspection->load(['items.head', 'reporter']);
        return view('cleaning_inspection_reports.show', compact('report'));
    }

    public function print(CleaningInspectionReport $cleaningInspection)
    {
        $report = $cleaningInspection->load(['items.head', 'reporter']);
        return view('cleaning_inspection_reports.print', compact('report'));
    }

    public function destroy(CleaningInspectionReport $cleaningInspection)
    {
        foreach ($cleaningInspection->items as $item) {
            if ($item->image_path) {
                Storage::delete('public/' . $item->image_path);
            }
        }
        $cleaningInspection->delete();

        return redirect()->route('cleaning-inspections.index')
            ->with('success', 'Report deleted.');
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function saveItems(CleaningInspectionReport $report, Request $request): void
    {
        // Reload existing items for image preservation
        $report->load('items');

        foreach ($request->input('items', []) as $headId => $itemData) {
            $statusVal = match ($itemData['status'] ?? 'na') {
                'yes'   => true,
                'no'    => false,
                default => null,
            };

            $existing  = $report->items->where('inspection_head_id', $headId)->first();
            $imagePath = $existing?->image_path;

            if ($request->hasFile("items.{$headId}.image")) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file("items.{$headId}.image")->store('inspection_images/cleaning', 'public');
            }

            CleaningInspectionReportItem::updateOrCreate(
                ['cleaning_inspection_report_id' => $report->id, 'inspection_head_id' => $headId],
                [
                    'status'     => $statusVal,
                    'image_path' => $imagePath,
                    'remarks'    => $itemData['remarks'] ?? null,
                ]
            );
        }
    }
}
