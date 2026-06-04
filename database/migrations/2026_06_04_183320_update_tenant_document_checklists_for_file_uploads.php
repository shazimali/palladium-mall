<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_document_checklists', function (Blueprint $table) {
            // Rename cnic_copy_tenant to cnic_copy_tenant_front
            $table->renameColumn('cnic_copy_tenant', 'cnic_copy_tenant_front');
        });

        Schema::table('tenant_document_checklists', function (Blueprint $table) {
            // Add cnic_copy_tenant_back
            $table->boolean('cnic_copy_tenant_back')->default(false)->after('cnic_copy_tenant_front');

            // Add upload columns for each other checklist item
            $table->string('cnic_copy_father_file')->nullable()->after('cnic_copy_father');
            $table->string('cnic_copy_guarantor_file')->nullable()->after('cnic_copy_guarantor');
            $table->string('passport_photo_file')->nullable()->after('passport_photo');
            $table->string('nikah_nama_file')->nullable()->after('nikah_nama');
            $table->string('frc_form_b_file')->nullable()->after('frc_form_b');
            $table->string('police_verification_file')->nullable()->after('police_verification');
            $table->string('tenant_application_form_file')->nullable()->after('tenant_application_form');
            $table->string('rules_acknowledgment_file')->nullable()->after('rules_acknowledgment');
            $table->string('inspection_report_file')->nullable()->after('inspection_report');
            $table->string('property_handover_form_file')->nullable()->after('property_handover_form');
            $table->string('meter_picture_file')->nullable()->after('meter_picture');
            $table->string('emergency_contacts_added_file')->nullable()->after('emergency_contacts_added');
            $table->string('guarantor_info_added_file')->nullable()->after('guarantor_info_added');
            $table->string('guarantor_business_card_file')->nullable()->after('guarantor_business_card');
            $table->string('tenant_business_card_file')->nullable()->after('tenant_business_card');
            $table->string('property_advisor_card_file')->nullable()->after('property_advisor_card');
            $table->string('old_tenant_verification_file')->nullable()->after('old_tenant_verification');
            $table->string('business_license_file')->nullable()->after('business_license');
            $table->string('utility_bills_clearance_file')->nullable()->after('utility_bills_clearance');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_document_checklists', function (Blueprint $table) {
            $table->dropColumn([
                'cnic_copy_tenant_back',
                'cnic_copy_father_file',
                'cnic_copy_guarantor_file',
                'passport_photo_file',
                'nikah_nama_file',
                'frc_form_b_file',
                'police_verification_file',
                'tenant_application_form_file',
                'rules_acknowledgment_file',
                'inspection_report_file',
                'property_handover_form_file',
                'meter_picture_file',
                'emergency_contacts_added_file',
                'guarantor_info_added_file',
                'guarantor_business_card_file',
                'tenant_business_card_file',
                'property_advisor_card_file',
                'old_tenant_verification_file',
                'business_license_file',
                'utility_bills_clearance_file',
            ]);
        });

        Schema::table('tenant_document_checklists', function (Blueprint $table) {
            $table->renameColumn('cnic_copy_tenant_front', 'cnic_copy_tenant');
        });
    }
};
