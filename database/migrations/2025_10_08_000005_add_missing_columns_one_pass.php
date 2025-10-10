<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // commissions
        if (Schema::hasTable('commissions')) {
            Schema::table('commissions', function (Blueprint $table) {
                if (!Schema::hasColumn('commissions', 'commission_id')) {
                    $table->string('commission_id')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('commissions', 'driver_match_id')) {
                    $table->unsignedBigInteger('driver_match_id')->nullable()->after('commission_id');
                }
                if (!Schema::hasColumn('commissions', 'rate')) {
                    $table->decimal('rate', 5, 2)->nullable()->after('driver_match_id');
                }
                if (!Schema::hasColumn('commissions', 'base_amount')) {
                    $table->decimal('base_amount', 10, 2)->nullable()->after('rate');
                }
                if (!Schema::hasColumn('commissions', 'due_date')) {
                    $table->timestamp('due_date')->nullable()->after('base_amount');
                }
                if (!Schema::hasColumn('commissions', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable()->after('due_date');
                }
                if (!Schema::hasColumn('commissions', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('paid_at');
                }
                if (!Schema::hasColumn('commissions', 'payment_reference')) {
                    $table->string('payment_reference')->nullable()->after('payment_method');
                }
                if (!Schema::hasColumn('commissions', 'disputed_at')) {
                    $table->timestamp('disputed_at')->nullable()->after('payment_reference');
                }
                if (!Schema::hasColumn('commissions', 'dispute_reason')) {
                    $table->text('dispute_reason')->nullable()->after('disputed_at');
                }
                if (!Schema::hasColumn('commissions', 'resolved_at')) {
                    $table->timestamp('resolved_at')->nullable()->after('dispute_reason');
                }
                if (!Schema::hasColumn('commissions', 'refunded_at')) {
                    $table->timestamp('refunded_at')->nullable()->after('resolved_at');
                }
                if (!Schema::hasColumn('commissions', 'refund_amount')) {
                    $table->decimal('refund_amount', 10, 2)->nullable()->after('refunded_at');
                }
                if (!Schema::hasColumn('commissions', 'refund_reason')) {
                    $table->text('refund_reason')->nullable()->after('refund_amount');
                }
                if (!Schema::hasColumn('commissions', 'notes')) {
                    $table->text('notes')->nullable()->after('refund_reason');
                }
            });
        }

        // companies
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (!Schema::hasColumn('companies', 'password')) {
                    $table->string('password')->nullable()->after('name');
                }
                if (!Schema::hasColumn('companies', 'email_verified_at')) {
                    $table->timestamp('email_verified_at')->nullable()->after('password');
                }
                if (!Schema::hasColumn('companies', 'remember_token')) {
                    $table->string('remember_token', 100)->nullable()->after('email_verified_at');
                }
            });
        }

        // company_requests
        if (Schema::hasTable('company_requests')) {
            Schema::table('company_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('company_requests', 'request_id')) {
                    $table->string('request_id')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('company_requests', 'position_title')) {
                    $table->string('position_title')->nullable()->after('request_id');
                }
                if (!Schema::hasColumn('company_requests', 'priority')) {
                    $table->string('priority')->nullable()->after('position_title');
                }
                if (!Schema::hasColumn('company_requests', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('priority');
                }
                if (!Schema::hasColumn('company_requests', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable()->after('created_by');
                }
                if (!Schema::hasColumn('company_requests', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('company_requests', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->after('approved_at');
                }
                if (!Schema::hasColumn('company_requests', 'queue_position')) {
                    $table->integer('queue_position')->nullable()->after('expires_at');
                }
                if (!Schema::hasColumn('company_requests', 'assigned_to')) {
                    $table->unsignedBigInteger('assigned_to')->nullable()->after('queue_position');
                }
                if (!Schema::hasColumn('company_requests', 'acceptance_notes')) {
                    $table->text('acceptance_notes')->nullable()->after('assigned_to');
                }
                if (!Schema::hasColumn('company_requests', 'estimated_completion')) {
                    $table->timestamp('estimated_completion')->nullable()->after('acceptance_notes');
                }
                if (!Schema::hasColumn('company_requests', 'accepted_at')) {
                    $table->timestamp('accepted_at')->nullable()->after('estimated_completion');
                }
                if (!Schema::hasColumn('company_requests', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('accepted_at');
                }
                if (!Schema::hasColumn('company_requests', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
                }
                if (!Schema::hasColumn('company_requests', 'cancelled_by')) {
                    $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
                }
                if (!Schema::hasColumn('company_requests', 'cancellation_reason')) {
                    $table->text('cancellation_reason')->nullable()->after('cancelled_by');
                }
                if (!Schema::hasColumn('company_requests', 'processing_notes')) {
                    $table->text('processing_notes')->nullable()->after('cancellation_reason');
                }
                if (!Schema::hasColumn('company_requests', 'started_at')) {
                    $table->timestamp('started_at')->nullable()->after('processing_notes');
                }
                if (!Schema::hasColumn('company_requests', 'completion_notes')) {
                    $table->text('completion_notes')->nullable()->after('started_at');
                }
                if (!Schema::hasColumn('company_requests', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('completion_notes');
                }
                if (!Schema::hasColumn('company_requests', 'rating')) {
                    $table->decimal('rating', 2, 1)->nullable()->after('completed_at');
                }
                if (!Schema::hasColumn('company_requests', 'pause_reason')) {
                    $table->string('pause_reason')->nullable()->after('rating');
                }
                if (!Schema::hasColumn('company_requests', 'paused_at')) {
                    $table->timestamp('paused_at')->nullable()->after('pause_reason');
                }
            });
        }

        // driver_matches
        if (Schema::hasTable('driver_matches')) {
            Schema::table('driver_matches', function (Blueprint $table) {
                if (!Schema::hasColumn('driver_matches', 'match_id')) {
                    $table->string('match_id')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('driver_matches', 'commission_rate')) {
                    $table->decimal('commission_rate', 5, 2)->nullable()->after('match_id');
                }
                if (!Schema::hasColumn('driver_matches', 'commission_amount')) {
                    $table->decimal('commission_amount', 10, 2)->nullable()->after('commission_rate');
                }
                if (!Schema::hasColumn('driver_matches', 'accepted_at')) {
                    $table->timestamp('accepted_at')->nullable()->after('commission_amount');
                }
                if (!Schema::hasColumn('driver_matches', 'declined_at')) {
                    $table->timestamp('declined_at')->nullable()->after('accepted_at');
                }
                if (!Schema::hasColumn('driver_matches', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('declined_at');
                }
                if (!Schema::hasColumn('driver_matches', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('completed_at');
                }
                if (!Schema::hasColumn('driver_matches', 'matched_by_admin')) {
                    $table->unsignedBigInteger('matched_by_admin')->nullable()->after('cancelled_at');
                }
                if (!Schema::hasColumn('driver_matches', 'auto_matched')) {
                    $table->boolean('auto_matched')->default(false)->after('matched_by_admin');
                }
                if (!Schema::hasColumn('driver_matches', 'driver_feedback')) {
                    $table->text('driver_feedback')->nullable()->after('auto_matched');
                }
                if (!Schema::hasColumn('driver_matches', 'company_feedback')) {
                    $table->text('company_feedback')->nullable()->after('driver_feedback');
                }
                if (!Schema::hasColumn('driver_matches', 'notes')) {
                    $table->text('notes')->nullable()->after('company_feedback');
                }
            });
        }

        // drivers (additional profile/KYC fields)
        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                if (!Schema::hasColumn('drivers', 'phone_verified_at')) {
                    $table->timestamp('phone_verified_at')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('drivers', 'phone_verification_status')) {
                    $table->string('phone_verification_status')->nullable()->after('phone_verified_at');
                }
                if (!Schema::hasColumn('drivers', 'email_verification_status')) {
                    $table->string('email_verification_status')->nullable()->after('phone_verification_status');
                }
                if (!Schema::hasColumn('drivers', 'state_of_origin')) {
                    $table->unsignedBigInteger('state_of_origin')->nullable()->after('email');
                }
                if (!Schema::hasColumn('drivers', 'lga_of_origin')) {
                    $table->unsignedBigInteger('lga_of_origin')->nullable()->after('state_of_origin');
                }
                if (!Schema::hasColumn('drivers', 'address_of_origin')) {
                    $table->text('address_of_origin')->nullable()->after('lga_of_origin');
                }
                if (!Schema::hasColumn('drivers', 'profile_photo')) {
                    $table->string('profile_photo')->nullable()->after('address_of_origin');
                }
                if (!Schema::hasColumn('drivers', 'residential_address')) {
                    $table->text('residential_address')->nullable()->after('profile_photo');
                }
                if (!Schema::hasColumn('drivers', 'marital_status')) {
                    $table->string('marital_status')->nullable()->after('residential_address');
                }
                if (!Schema::hasColumn('drivers', 'emergency_contact_name')) {
                    $table->string('emergency_contact_name')->nullable()->after('marital_status');
                }
                if (!Schema::hasColumn('drivers', 'emergency_contact_phone')) {
                    $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
                }
                if (!Schema::hasColumn('drivers', 'emergency_contact_relationship')) {
                    $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
                }
                if (!Schema::hasColumn('drivers', 'full_address')) {
                    $table->text('full_address')->nullable()->after('emergency_contact_relationship');
                }
                if (!Schema::hasColumn('drivers', 'city')) {
                    $table->string('city')->nullable()->after('full_address');
                }
                if (!Schema::hasColumn('drivers', 'postal_code')) {
                    $table->string('postal_code')->nullable()->after('city');
                }
                if (!Schema::hasColumn('drivers', 'driver_license_scan')) {
                    $table->string('driver_license_scan')->nullable()->after('postal_code');
                }
                if (!Schema::hasColumn('drivers', 'national_id')) {
                    $table->string('national_id')->nullable()->after('driver_license_scan');
                }
                if (!Schema::hasColumn('drivers', 'passport_photo')) {
                    $table->string('passport_photo')->nullable()->after('national_id');
                }
                if (!Schema::hasColumn('drivers', 'bvn_number')) {
                    $table->string('bvn_number')->nullable()->after('passport_photo');
                }
                if (!Schema::hasColumn('drivers', 'created_by_admin_id')) {
                    $table->unsignedBigInteger('created_by_admin_id')->nullable()->after('bvn_number');
                }
                if (!Schema::hasColumn('drivers', 'state_id')) {
                    $table->unsignedBigInteger('state_id')->nullable()->after('created_by_admin_id');
                }
                if (!Schema::hasColumn('drivers', 'lga_id')) {
                    $table->unsignedBigInteger('lga_id')->nullable()->after('state_id');
                }
                if (!Schema::hasColumn('drivers', 'years_of_experience')) {
                    $table->integer('years_of_experience')->nullable()->after('lga_id');
                }
                if (!Schema::hasColumn('drivers', 'previous_company')) {
                    $table->string('previous_company')->nullable()->after('years_of_experience');
                }
                if (!Schema::hasColumn('drivers', 'has_vehicle')) {
                    $table->boolean('has_vehicle')->nullable()->after('previous_company');
                }
                if (!Schema::hasColumn('drivers', 'vehicle_type')) {
                    $table->string('vehicle_type')->nullable()->after('has_vehicle');
                }
                if (!Schema::hasColumn('drivers', 'vehicle_year')) {
                    $table->integer('vehicle_year')->nullable()->after('vehicle_type');
                }
                if (!Schema::hasColumn('drivers', 'bank_id')) {
                    $table->unsignedBigInteger('bank_id')->nullable()->after('vehicle_year');
                }
                if (!Schema::hasColumn('drivers', 'account_number')) {
                    $table->string('account_number')->nullable()->after('bank_id');
                }
                if (!Schema::hasColumn('drivers', 'account_name')) {
                    $table->string('account_name')->nullable()->after('account_number');
                }
                if (!Schema::hasColumn('drivers', 'bvn')) {
                    $table->string('bvn')->nullable()->after('account_name');
                }
                if (!Schema::hasColumn('drivers', 'preferred_work_location')) {
                    $table->string('preferred_work_location')->nullable()->after('bvn');
                }
                if (!Schema::hasColumn('drivers', 'available_for_night_shifts')) {
                    $table->boolean('available_for_night_shifts')->nullable()->after('preferred_work_location');
                }
                if (!Schema::hasColumn('drivers', 'available_for_weekend_work')) {
                    $table->boolean('available_for_weekend_work')->nullable()->after('available_for_night_shifts');
                }
                if (!Schema::hasColumn('drivers', 'registration_date')) {
                    $table->timestamp('registration_date')->nullable()->after('available_for_weekend_work');
                }
                if (!Schema::hasColumn('drivers', 'drivers_license_photo_path')) {
                    $table->string('drivers_license_photo_path')->nullable()->after('registration_date');
                }
                if (!Schema::hasColumn('drivers', 'profile_photo_path')) {
                    $table->string('profile_photo_path')->nullable()->after('drivers_license_photo_path');
                }
                if (!Schema::hasColumn('drivers', 'national_id_path')) {
                    $table->string('national_id_path')->nullable()->after('profile_photo_path');
                }
                if (!Schema::hasColumn('drivers', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('national_id_path');
                }
                if (!Schema::hasColumn('drivers', 'state')) {
                    $table->string('state')->nullable()->after('created_by');
                }
                if (!Schema::hasColumn('drivers', 'national_id_image')) {
                    $table->string('national_id_image')->nullable()->after('state');
                }
                if (!Schema::hasColumn('drivers', 'proof_of_address_path')) {
                    $table->string('proof_of_address_path')->nullable()->after('national_id_image');
                }
                if (!Schema::hasColumn('drivers', 'guarantor_letter_path')) {
                    $table->string('guarantor_letter_path')->nullable()->after('proof_of_address_path');
                }
                if (!Schema::hasColumn('drivers', 'vehicle_registration_path')) {
                    $table->string('vehicle_registration_path')->nullable()->after('guarantor_letter_path');
                }
                if (!Schema::hasColumn('drivers', 'insurance_certificate_path')) {
                    $table->string('insurance_certificate_path')->nullable()->after('vehicle_registration_path');
                }
                if (!Schema::hasColumn('drivers', 'available')) {
                    $table->boolean('available')->nullable()->after('insurance_certificate_path');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This migration is additive; rolling back will attempt to drop created columns if they exist.
        if (Schema::hasTable('commissions')) {
            Schema::table('commissions', function (Blueprint $table) {
                $cols = [];
                foreach ([
                    'commission_id','driver_match_id','rate','base_amount','due_date','paid_at','payment_method','payment_reference',
                    'disputed_at','dispute_reason','resolved_at','refunded_at','refund_amount','refund_reason','notes'
                ] as $c) {
                    if (Schema::hasColumn('commissions', $c)) $cols[] = $c;
                }
                if ($cols) $table->dropColumn($cols);
            });
        }

        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                $cols = [];
                foreach (['password','email_verified_at','remember_token'] as $c) {
                    if (Schema::hasColumn('companies', $c)) $cols[] = $c;
                }
                if ($cols) $table->dropColumn($cols);
            });
        }

        if (Schema::hasTable('company_requests')) {
            Schema::table('company_requests', function (Blueprint $table) {
                $cols = [];
                foreach ([
                    'request_id','position_title','priority','created_by','approved_by','approved_at','expires_at','queue_position','assigned_to',
                    'acceptance_notes','estimated_completion','accepted_at','rejected_at','cancelled_at','cancelled_by','cancellation_reason','processing_notes',
                    'started_at','completion_notes','completed_at','rating','pause_reason','paused_at'
                ] as $c) {
                    if (Schema::hasColumn('company_requests', $c)) $cols[] = $c;
                }
                if ($cols) $table->dropColumn($cols);
            });
        }

        if (Schema::hasTable('driver_matches')) {
            Schema::table('driver_matches', function (Blueprint $table) {
                $cols = [];
                foreach ([
                    'match_id','commission_rate','commission_amount','accepted_at','declined_at','completed_at','cancelled_at','matched_by_admin','auto_matched','driver_feedback','company_feedback','notes'
                ] as $c) {
                    if (Schema::hasColumn('driver_matches', $c)) $cols[] = $c;
                }
                if ($cols) $table->dropColumn($cols);
            });
        }

        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                $cols = [];
                foreach ([
                    'phone_verified_at','phone_verification_status','email_verification_status','state_of_origin','lga_of_origin','address_of_origin','profile_photo',
                    'residential_address','marital_status','emergency_contact_name','emergency_contact_phone','emergency_contact_relationship','full_address','city','postal_code',
                    'driver_license_scan','national_id','passport_photo','bvn_number','created_by_admin_id','state_id','lga_id','years_of_experience','previous_company',
                    'has_vehicle','vehicle_type','vehicle_year','bank_id','account_number','account_name','bvn','preferred_work_location','available_for_night_shifts',
                    'available_for_weekend_work','registration_date','drivers_license_photo_path','profile_photo_path','national_id_path','created_by','state','national_id_image',
                    'proof_of_address_path','guarantor_letter_path','vehicle_registration_path','insurance_certificate_path','available'
                ] as $c) {
                    if (Schema::hasColumn('drivers', $c)) $cols[] = $c;
                }
                if ($cols) $table->dropColumn($cols);
            });
        }
    }
};
