<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Clean tenants phone and whatsapp_number
        DB::table('tenants')->get()->each(function ($tenant) {
            $cleanPhone = $tenant->phone ? preg_replace('/[^\d+]/', '', $tenant->phone) : '';
            $cleanWhatsapp = $tenant->whatsapp_number ? preg_replace('/[^\d+]/', '', $tenant->whatsapp_number) : null;
            DB::table('tenants')->where('id', $tenant->id)->update([
                'phone' => $cleanPhone,
                'whatsapp_number' => $cleanWhatsapp,
            ]);
        });

        // Clean guarantors phone
        DB::table('guarantors')->get()->each(function ($guarantor) {
            $cleanPhone = $guarantor->phone ? preg_replace('/[^\d+]/', '', $guarantor->phone) : '';
            DB::table('guarantors')->where('id', $guarantor->id)->update([
                'phone' => $cleanPhone,
            ]);
        });

        // Clean emergency_contacts phone
        DB::table('emergency_contacts')->get()->each(function ($contact) {
            $cleanPhone = $contact->phone ? preg_replace('/[^\d+]/', '', $contact->phone) : '';
            DB::table('emergency_contacts')->where('id', $contact->id)->update([
                'phone' => $cleanPhone,
            ]);
        });
    }

    public function down(): void
    {
        // Rollback is not necessary/possible for data format cleanup
    }
};
