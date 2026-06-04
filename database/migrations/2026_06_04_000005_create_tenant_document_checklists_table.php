<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_document_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();

            // Basic Identity
            $table->boolean('cnic_copy_tenant')->default(false);
            $table->boolean('cnic_copy_father')->default(false);
            $table->boolean('cnic_copy_guarantor')->default(false);
            $table->boolean('passport_photo')->default(false);
            $table->boolean('nikah_nama')->default(false);
            $table->boolean('frc_form_b')->default(false);
            $table->boolean('police_verification')->default(false);

            // Application & Agreement
            $table->boolean('tenant_application_form')->default(false);
            $table->boolean('tenancy_agreement_copy')->default(false);
            $table->boolean('rules_acknowledgment')->default(false);

            // Property & Security
            $table->boolean('inspection_report')->default(false);
            $table->boolean('property_handover_form')->default(false);
            $table->boolean('security_deposit_receipt')->default(false);
            $table->boolean('meter_picture')->default(false);

            // Contact & References
            $table->boolean('emergency_contacts_added')->default(false);
            $table->boolean('guarantor_info_added')->default(false);
            $table->boolean('guarantor_business_card')->default(false);
            $table->boolean('tenant_business_card')->default(false);
            $table->boolean('property_advisor_card')->default(false);
            $table->boolean('old_tenant_verification')->default(false);

            // Commercial Only
            $table->boolean('business_license')->default(false);
            $table->boolean('utility_bills_clearance')->default(false);

            // File uploads (paths)
            $table->string('cnic_front_image')->nullable();
            $table->string('cnic_back_image')->nullable();
            $table->string('signed_agreement_scan')->nullable();
            $table->string('bank_voucher')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_document_checklists');
    }
};
