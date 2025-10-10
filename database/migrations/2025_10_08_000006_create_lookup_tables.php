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
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('iso2', 2)->nullable();
                $table->string('iso3', 3)->nullable();
                $table->string('country_code')->nullable();
                $table->timestamps();
            });
        }

        // global_states
        if (!Schema::hasTable('global_states')) {
            Schema::create('global_states', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->string('name');
                $table->string('code')->nullable();
                $table->timestamps();

                $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            });
        }

        // global_cities
        if (!Schema::hasTable('global_cities')) {
            Schema::create('global_cities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('state_id')->nullable();
                $table->string('name');
                $table->timestamps();

                $table->foreign('state_id')->references('id')->on('global_states')->onDelete('cascade');
            });
        }

        // global_vehicle_types
        if (!Schema::hasTable('global_vehicle_types')) {
            Schema::create('global_vehicle_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->timestamps();
            });
        }

        // global_languages
        if (!Schema::hasTable('global_languages')) {
            Schema::create('global_languages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->timestamps();
            });
        }

        // driver_category_requirements
        if (!Schema::hasTable('driver_category_requirements')) {
            Schema::create('driver_category_requirements', function (Blueprint $table) {
                $table->id();
                $table->string('category')->nullable();
                $table->text('requirements')->nullable();
                $table->timestamps();
            });
        }

        // roles
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name')->nullable()->default('web');
                $table->timestamps();
            });
        }

        // permissions
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name')->nullable()->default('web');
                $table->timestamps();
            });
        }

        // settings
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // user_activities
        if (!Schema::hasTable('user_activities')) {
            Schema::create('user_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_type')->nullable();
                $table->string('action');
                $table->text('meta')->nullable();
                $table->timestamps();
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
        foreach ([
            'user_activities', 'settings', 'permissions', 'roles', 'driver_category_requirements',
            'global_languages', 'global_vehicle_types', 'global_cities', 'global_states', 'countries'
        ] as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }
    }
};
