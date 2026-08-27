<?php

use App\Models\PerformanceTaskTemplate;
use App\Models\ReportType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $reportTypes = ReportType::all();
        if ($reportTypes->isEmpty()) {
            return;
        }

        $templates = PerformanceTaskTemplate::where('type', 'dynamic_report')
            ->whereNull('report_type_id')
            ->get();

        foreach ($templates as $template) {
            $matched = $reportTypes->first(function ($rt) use ($template) {
                return strtolower(trim($rt->name)) === strtolower(trim($template->name))
                    || strtolower(trim($rt->key)) === strtolower(str_replace(' ', '_', trim($template->name)));
            });

            if ($matched) {
                $template->update([
                    'report_type_id' => $matched->id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed for data backfill
    }
};
