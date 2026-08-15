<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\FlatInspectionReport;
use App\Models\FlatInspectionReportItem;
use App\Models\InspectionHead;
use App\Models\InspectionPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FlatInspectionReportController extends Controller
{
    /**
     * Show form to create (or edit) a flat inspection report for an agreement.
     */
    public function create(Request $request)
    {
        $agreementId = $request->agreement_id;
        $type        = $request->get('type', 'move_in'); // move_in | move_out

        $agreement = Agreement::with(['tenant', 'unit.floor', 'unit.block'])->findOrFail($agreementId);

        // Load existing report if it exists (for edit mode)
        $report = FlatInspectionReport::with('items')
            ->where('agreement_id', $agreementId)
            ->where('type', $type)
            ->first();

        $heads = InspectionHead::active()->flatInspection()->orderBy('sort_order')->orderBy('name')->get();

        // Map existing item statuses by head_id for easy access in view
        $existingItems = $report
            ? $report->items->keyBy('inspection_head_id')
            : collect();

        $inspectionPersons = InspectionPerson::orderBy('name')->get();

        return view('flat_inspection_reports.create', compact(
            'agreement', 'type', 'report', 'heads', 'existingItems', 'inspectionPersons'
        ));
    }

    /**
     * Save or update a flat inspection report.
     */
    public function store(Request $request)
    {
        $request->validate([
            'agreement_id'         => 'required|exists:agreements,id',
            'type'                 => 'required|in:move_in,move_out',
            'inspected_at'         => 'nullable|date',
            'inspection_member'    => 'nullable|string|max:255',
            'inspection_person_id' => 'nullable|exists:inspection_persons,id',
            'flat_condition'       => 'nullable|in:good,average,poor',
            'remarks'              => 'nullable|string',
            'items'                => 'nullable|array',
            'items.*.status'       => 'nullable|in:pass,fail,na',
            'items.*.remarks'      => 'nullable|string|max:1000',
            'items.*.image'        => 'nullable|image|max:200', // 200 KB
        ]);

        $agreement = Agreement::findOrFail($request->agreement_id);

        // Upsert the report
        $report = FlatInspectionReport::updateOrCreate(
            ['agreement_id' => $agreement->id, 'type' => $request->type],
            [
                'tenant_id'            => $agreement->tenant_id,
                'inspected_by'         => Auth::id(),
                'inspection_member'    => $request->inspection_member,
                'inspection_person_id' => $request->inspection_person_id,
                'inspected_at'         => $request->inspected_at,
                'flat_condition'       => $request->flat_condition,
                'remarks'              => $request->remarks,
            ]
        );

        // Process each head's item
        foreach ($request->input('items', []) as $headId => $itemData) {
            $statusVal = match ($itemData['status'] ?? 'na') {
                'pass' => true,
                'fail' => false,
                default => null,
            };

            $existing  = $report->items->where('inspection_head_id', $headId)->first();
            $imagePath = $existing?->image_path;

            if ($request->hasFile("items.{$headId}.image")) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file("items.{$headId}.image")->store('inspection_images/flat', 'public');
            }

            FlatInspectionReportItem::updateOrCreate(
                ['flat_inspection_report_id' => $report->id, 'inspection_head_id' => $headId],
                [
                    'status'     => $statusVal,
                    'image_path' => $imagePath,
                    'remarks'    => $itemData['remarks'] ?? null,
                ]
            );
        }

        $typeLabel = $request->type === 'move_in' ? 'Move In' : 'Move Out';
        return redirect()->route('agreements.show', $agreement)
            ->with('success', "{$typeLabel} Flat Inspection saved successfully.");
    }

    /**
     * Show a submitted flat inspection report (read-only).
     */
    public function show(FlatInspectionReport $flatInspectionReport)
    {
        $report = $flatInspectionReport->load(['agreement.tenant', 'agreement.unit.floor', 'items.head', 'inspectionPerson', 'inspector']);
        return view('flat_inspection_reports.show', compact('report'));
    }

    /**
     * Print view for a flat inspection report.
     */
    public function print(FlatInspectionReport $flatInspectionReport)
    {
        $report = $flatInspectionReport->load(['agreement.tenant', 'agreement.unit.floor', 'items.head', 'inspectionPerson', 'inspector']);
        return view('flat_inspection_reports.print', compact('report'));
    }

    /**
     * Delete a flat inspection report.
     */
    public function destroy(FlatInspectionReport $flatInspectionReport)
    {
        // Delete item images from storage
        foreach ($flatInspectionReport->items as $item) {
            if ($item->image_path) {
                Storage::delete('public/' . $item->image_path);
            }
        }
        $flatInspectionReport->delete();

        return back()->with('success', 'Flat inspection report deleted.');
    }
}
