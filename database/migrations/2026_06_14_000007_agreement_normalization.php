<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Alter tenant_document_checklists - drop foreign key and unique constraint on tenant_id
        Schema::table('tenant_document_checklists', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique('tenant_document_checklists_tenant_id_unique');
        });

        // 2. Re-add foreign key on tenant_id without unique constraint
        Schema::table('tenant_document_checklists', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // 3. Add agreement_id to tables
        Schema::table('guarantors', function (Blueprint $table) {
            $table->foreignId('agreement_id')->nullable()->constrained('agreements')->cascadeOnDelete();
        });

        Schema::table('tenant_partners', function (Blueprint $table) {
            $table->foreignId('agreement_id')->nullable()->constrained('agreements')->cascadeOnDelete();
        });

        Schema::table('emergency_contacts', function (Blueprint $table) {
            $table->foreignId('agreement_id')->nullable()->constrained('agreements')->cascadeOnDelete();
        });

        Schema::table('tenant_document_checklists', function (Blueprint $table) {
            $table->foreignId('agreement_id')->nullable()->constrained('agreements')->cascadeOnDelete();
        });

        // 4. Populate agreement_id for existing records
        $this->populateAgreementIds();

        // 5. Now make agreement_id unique in tenant_document_checklists
        Schema::table('tenant_document_checklists', function (Blueprint $table) {
            $table->unique('agreement_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_document_checklists', function (Blueprint $table) {
            $table->dropUnique(['agreement_id']);
            $table->dropForeign(['agreement_id']);
            $table->dropColumn('agreement_id');
        });

        Schema::table('guarantors', function (Blueprint $table) {
            $table->dropForeign(['agreement_id']);
            $table->dropColumn('agreement_id');
        });

        Schema::table('tenant_partners', function (Blueprint $table) {
            $table->dropForeign(['agreement_id']);
            $table->dropColumn('agreement_id');
        });

        Schema::table('emergency_contacts', function (Blueprint $table) {
            $table->dropForeign(['agreement_id']);
            $table->dropColumn('agreement_id');
        });

        Schema::table('tenant_document_checklists', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('tenant_document_checklists', function (Blueprint $table) {
            $table->unique('tenant_id');
        });

        Schema::table('tenant_document_checklists', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    private function populateAgreementIds(): void
    {
        $tables = ['guarantors', 'tenant_partners', 'emergency_contacts', 'tenant_document_checklists'];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $records = DB::table($table)->get();
            foreach ($records as $record) {
                if (isset($record->tenant_id) && $record->tenant_id) {
                    // Find active or latest agreement for this tenant
                    $agreementId = DB::table('agreements')
                        ->where('tenant_id', $record->tenant_id)
                        ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                        ->orderBy('id', 'desc')
                        ->value('id');

                    if ($agreementId) {
                        DB::table($table)
                            ->where('id', $record->id)
                            ->update(['agreement_id' => $agreementId]);
                    }
                }
            }
        }
    }
};
