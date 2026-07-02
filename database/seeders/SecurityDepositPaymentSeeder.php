<?php

namespace Database\Seeders;

use App\Models\Agreement;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class SecurityDepositPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clean up any security deposit payments linked to non-active/draft agreements or missing agreements
        $invalidPayments = Payment::where('type', 'security_deposit')
            ->where(function ($query) {
                $query->whereHas('agreement', function ($q) {
                    $q->where('status', '!=', 'active');
                })->orWhereNull('agreement_id');
            })
            ->get();

        foreach ($invalidPayments as $p) {
            $p->forceDelete();
        }

        // 2. Seed missing security deposit payments for active agreements
        $agreements = Agreement::where('status', 'active')
            ->where('security_deposit', '>', 0)
            ->whereNotNull('tenant_id')
            ->get();

        foreach ($agreements as $agreement) {
            $exists = Payment::where('agreement_id', $agreement->id)
                ->where('type', 'security_deposit')
                ->exists();

            if (!$exists) {
                $month = $agreement->start_date 
                    ? $agreement->start_date->copy()->startOfMonth()->toDateString() 
                    : now()->startOfMonth()->toDateString();
                $dueDate = $agreement->start_date 
                    ? $agreement->start_date->toDateString() 
                    : now()->toDateString();

                Payment::create([
                    'tenant_id'    => $agreement->tenant_id,
                    'unit_id'      => $agreement->unit_id,
                    'agreement_id' => $agreement->id,
                    'type'         => 'security_deposit',
                    'month'        => $month,
                    'amount'       => $agreement->security_deposit,
                    'amount_paid'  => 0,
                    'status'       => 'unpaid',
                    'due_date'     => $dueDate,
                ]);
            }
        }
    }
}
