<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProfitLossExport implements FromView, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected array $data,
        protected string $title
    ) {}

    public function view(): View
    {
        return view('reports.profit_loss_excel', $this->data);
    }

    public function title(): string
    {
        return $this->title;
    }
}
