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
        // countries
        if (Schema::hasTable('countries')) {
            Schema::table('countries', function (Blueprint $table) {
                if (!Schema::hasColumn('countries', 'iso_code_2')) $table->string('iso_code_2', 2)->nullable()->after('name');
                if (!Schema::hasColumn('countries', 'iso_code_3')) $table->string('iso_code_3', 3)->nullable()->after('iso_code_2');
                if (!Schema::hasColumn('countries', 'phone_code')) $table->string('phone_code', 10)->nullable()->after('iso_code_3');
                if (!Schema::hasColumn('countries', 'currency_code')) $table->string('currency_code', 10)->nullable()->after('phone_code');
                if (!Schema::hasColumn('countries', 'currency_symbol')) $table->string('currency_symbol', 10)->nullable()->after('currency_code');
                if (!Schema::hasColumn('countries', 'timezone')) $table->string('timezone')->nullable()->after('currency_symbol');
                if (!Schema::hasColumn('countries', 'common_languages')) $table->json('common_languages')->nullable()->after('timezone');
                if (!Schema::hasColumn('countries', 'continent')) $table->string('continent')->nullable()->after('common_languages');
                if (!Schema::hasColumn('countries', 'is_active')) $table->boolean('is_active')->default(true)->after('continent');
                if (!Schema::hasColumn('countries', 'is_supported_market')) $table->boolean('is_supported_market')->default(false)->after('is_active');
                if (!Schema::hasColumn('countries', 'priority_order')) $table->integer('priority_order')->nullable()->after('is_supported_market');
            });
        }

        // driver_category_requirements
        if (Schema::hasTable('driver_category_requirements')) {
            Schema::table('driver_category_requirements', function (Blueprint $table) {
                if (!Schema::hasColumn('driver_category_requirements', 'country_id')) $table->unsignedBigInteger('country_id')->nullable()->after('id');
                if (!Schema::hasColumn('driver_category_requirements', 'required_licenses')) $table->json('required_licenses')->nullable()->after('country_id');
                if (!Schema::hasColumn('driver_category_requirements', 'required_certifications')) $table->json('required_certifications')->nullable()->after('required_licenses');
                if (!Schema::hasColumn('driver_category_requirements', 'required_documents')) $table->json('required_documents')->nullable()->after('required_certifications');
                if (!Schema::hasColumn('driver_category_requirements', 'background_check_requirements')) $table->text('background_check_requirements')->nullable()->after('required_documents');
                if (!Schema::hasColumn('driver_category_requirements', 'minimum_experience_years')) $table->integer('minimum_experience_years')->nullable()->after('background_check_requirements');
                if (!Schema::hasColumn('driver_category_requirements', 'vehicle_requirements')) $table->json('vehicle_requirements')->nullable()->after('minimum_experience_years');
                if (!Schema::hasColumn('driver_category_requirements', 'is_active')) $table->boolean('is_active')->default(true)->after('vehicle_requirements');
            });
        }

        // global_cities
        if (Schema::hasTable('global_cities')) {
            Schema::table('global_cities', function (Blueprint $table) {
                if (!Schema::hasColumn('global_cities', 'country_id')) $table->unsignedBigInteger('country_id')->nullable()->after('id');
                if (!Schema::hasColumn('global_cities', 'type')) $table->string('type')->nullable()->after('country_id');
                if (!Schema::hasColumn('global_cities', 'latitude')) $table->decimal('latitude', 10, 7)->nullable()->after('type');
                if (!Schema::hasColumn('global_cities', 'longitude')) $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
                if (!Schema::hasColumn('global_cities', 'is_major_city')) $table->boolean('is_major_city')->default(false)->after('longitude');
                if (!Schema::hasColumn('global_cities', 'is_active')) $table->boolean('is_active')->default(true)->after('is_major_city');
            });
        }

        // global_languages
        if (Schema::hasTable('global_languages')) {
            Schema::table('global_languages', function (Blueprint $table) {
                if (!Schema::hasColumn('global_languages', 'native_name')) $table->string('native_name')->nullable()->after('name');
                if (!Schema::hasColumn('global_languages', 'is_major_language')) $table->boolean('is_major_language')->default(false)->after('native_name');
                if (!Schema::hasColumn('global_languages', 'is_active')) $table->boolean('is_active')->default(true)->after('is_major_language');
            });
        }

        // global_states
        if (Schema::hasTable('global_states')) {
            Schema::table('global_states', function (Blueprint $table) {
                if (!Schema::hasColumn('global_states', 'type')) $table->string('type')->nullable()->after('name');
                if (!Schema::hasColumn('global_states', 'is_active')) $table->boolean('is_active')->default(true)->after('type');
            });
        }

        // global_vehicle_types
        if (Schema::hasTable('global_vehicle_types')) {
            Schema::table('global_vehicle_types', function (Blueprint $table) {
                if (!Schema::hasColumn('global_vehicle_types', 'category')) $table->string('category')->nullable()->after('name');
                if (!Schema::hasColumn('global_vehicle_types', 'sub_category')) $table->string('sub_category')->nullable()->after('category');
                if (!Schema::hasColumn('global_vehicle_types', 'specifications')) $table->json('specifications')->nullable()->after('sub_category');
                if (!Schema::hasColumn('global_vehicle_types', 'license_requirements')) $table->json('license_requirements')->nullable()->after('specifications');
                if (!Schema::hasColumn('global_vehicle_types', 'requires_special_training')) $table->boolean('requires_special_training')->default(false)->after('license_requirements');
                if (!Schema::hasColumn('global_vehicle_types', 'is_active')) $table->boolean('is_active')->default(true)->after('requires_special_training');
                if (!Schema::hasColumn('global_vehicle_types', 'sort_order')) $table->integer('sort_order')->nullable()->after('is_active');
            });
        }

        // permissions
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                if (!Schema::hasColumn('permissions', 'display_name')) $table->string('display_name')->nullable()->after('name');
                if (!Schema::hasColumn('permissions', 'description')) $table->text('description')->nullable()->after('display_name');
                if (!Schema::hasColumn('permissions', 'category')) $table->string('category')->nullable()->after('description');
                if (!Schema::hasColumn('permissions', 'resource')) $table->string('resource')->nullable()->after('category');
                if (!Schema::hasColumn('permissions', 'action')) $table->string('action')->nullable()->after('resource');
                if (!Schema::hasColumn('permissions', 'is_active')) $table->boolean('is_active')->default(true)->after('action');
                if (!Schema::hasColumn('permissions', 'meta')) $table->json('meta')->nullable()->after('is_active');
                if (!Schema::hasColumn('permissions', 'deleted_at')) $table->softDeletes()->nullable();
            });
        }

        // roles
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (!Schema::hasColumn('roles', 'display_name')) $table->string('display_name')->nullable()->after('name');
                if (!Schema::hasColumn('roles', 'description')) $table->text('description')->nullable()->after('display_name');
                if (!Schema::hasColumn('roles', 'level')) $table->integer('level')->nullable()->after('description');
                if (!Schema::hasColumn('roles', 'is_active')) $table->boolean('is_active')->default(true)->after('level');
                if (!Schema::hasColumn('roles', 'meta')) $table->json('meta')->nullable()->after('is_active');
                if (!Schema::hasColumn('roles', 'deleted_at')) $table->softDeletes()->nullable();
            });
        }

        // settings
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'type')) $table->string('type')->nullable()->after('key');
                if (!Schema::hasColumn('settings', 'description')) $table->text('description')->nullable()->after('type');
                if (!Schema::hasColumn('settings', 'group')) $table->string('group')->nullable()->after('description');
                if (!Schema::hasColumn('settings', 'is_public')) $table->boolean('is_public')->default(false)->after('group');
                if (!Schema::hasColumn('settings', 'validation_rules')) $table->json('validation_rules')->nullable()->after('is_public');
                if (!Schema::hasColumn('settings', 'created_by')) $table->unsignedBigInteger('created_by')->nullable()->after('validation_rules');
                if (!Schema::hasColumn('settings', 'updated_by')) $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                if (!Schema::hasColumn('settings', 'deleted_at')) $table->softDeletes()->nullable();
            });
        }

        // user_activities
        if (Schema::hasTable('user_activities')) {
            Schema::table('user_activities', function (Blueprint $table) {
                if (!Schema::hasColumn('user_activities', 'description')) $table->text('description')->nullable()->after('action');
                if (!Schema::hasColumn('user_activities', 'model_type')) $table->string('model_type')->nullable()->after('description');
                if (!Schema::hasColumn('user_activities', 'model_id')) $table->unsignedBigInteger('model_id')->nullable()->after('model_type');
                if (!Schema::hasColumn('user_activities', 'old_values')) $table->json('old_values')->nullable()->after('model_id');
                if (!Schema::hasColumn('user_activities', 'new_values')) $table->json('new_values')->nullable()->after('old_values');
                if (!Schema::hasColumn('user_activities', 'ip_address')) $table->string('ip_address', 45)->nullable()->after('new_values');
                if (!Schema::hasColumn('user_activities', 'user_agent')) $table->string('user_agent', 500)->nullable()->after('ip_address');
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
        // For safety, do not drop columns automatically in production without review.
        // This down() will attempt to remove added columns if you run rollback in development.

        if (Schema::hasTable('user_activities')) {
            Schema::table('user_activities', function (Blueprint $table) {
                $cols = ['description','model_type','model_id','old_values','new_values','ip_address','user_agent'];
                foreach ($cols as $c) if (Schema::hasColumn('user_activities', $c)) $table->dropColumn($c);
            });
        }

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $cols = ['type','description','group','is_public','validation_rules','created_by','updated_by','deleted_at'];
                foreach ($cols as $c) if (Schema::hasColumn('settings', $c)) $table->dropColumn($c);
            });
        }

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                $cols = ['display_name','description','level','is_active','meta','deleted_at'];
                foreach ($cols as $c) if (Schema::hasColumn('roles', $c)) $table->dropColumn($c);
            });
        }

        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                $cols = ['display_name','description','category','resource','action','is_active','meta','deleted_at'];
                foreach ($cols as $c) if (Schema::hasColumn('permissions', $c)) $table->dropColumn($c);
            });
        }

        if (Schema::hasTable('global_vehicle_types')) {
            Schema::table('global_vehicle_types', function (Blueprint $table) {
                $cols = ['category','sub_category','specifications','license_requirements','requires_special_training','is_active','sort_order'];
                foreach ($cols as $c) if (Schema::hasColumn('global_vehicle_types', $c)) $table->dropColumn($c);
            });
        }

        if (Schema::hasTable('global_states')) {
            Schema::table('global_states', function (Blueprint $table) {
                $cols = ['type','is_active'];
                foreach ($cols as $c) if (Schema::hasColumn('global_states', $c)) $table->dropColumn($c);
            });
        }

        if (Schema::hasTable('global_languages')) {
            Schema::table('global_languages', function (Blueprint $table) {
                $cols = ['native_name','is_major_language','is_active'];
                foreach ($cols as $c) if (Schema::hasColumn('global_languages', $c)) $table->dropColumn($c);
            });
        }

        if (Schema::hasTable('global_cities')) {
            Schema::table('global_cities', function (Blueprint $table) {
                $cols = ['country_id','type','latitude','longitude','is_major_city','is_active'];
                foreach ($cols as $c) if (Schema::hasColumn('global_cities', $c)) $table->dropColumn($c);
            });
        }

        if (Schema::hasTable('driver_category_requirements')) {
            Schema::table('driver_category_requirements', function (Blueprint $table) {
                $cols = ['country_id','required_licenses','required_certifications','required_documents','background_check_requirements','minimum_experience_years','vehicle_requirements','is_active'];
                foreach ($cols as $c) if (Schema::hasColumn('driver_category_requirements', $c)) $table->dropColumn($c);
            });
        }

        if (Schema::hasTable('countries')) {
            Schema::table('countries', function (Blueprint $table) {
                $cols = ['iso_code_2','iso_code_3','phone_code','currency_code','currency_symbol','timezone','common_languages','continent','is_active','is_supported_market','priority_order'];
                foreach ($cols as $c) if (Schema::hasColumn('countries', $c)) $table->dropColumn($c);
            });
        }
    }
};
