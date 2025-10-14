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
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name')->nullable();
                $table->text('description')->nullable();
                $table->string('category')->nullable();
                $table->string('resource')->nullable();
                $table->string('action')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('meta')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        } else {
            // If table already exists, add missing columns
            Schema::table('permissions', function (Blueprint $table) {
                if (!Schema::hasColumn('permissions', 'display_name')) {
                    $table->string('display_name')->nullable()->after('name');
                }
                if (!Schema::hasColumn('permissions', 'description')) {
                    $table->text('description')->nullable()->after('display_name');
                }
                if (!Schema::hasColumn('permissions', 'category')) {
                    $table->string('category')->nullable()->after('description');
                }
                if (!Schema::hasColumn('permissions', 'resource')) {
                    $table->string('resource')->nullable()->after('category');
                }
                if (!Schema::hasColumn('permissions', 'action')) {
                    $table->string('action')->nullable()->after('resource');
                }
                if (!Schema::hasColumn('permissions', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('action');
                }
                if (!Schema::hasColumn('permissions', 'meta')) {
                    $table->json('meta')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('permissions', 'deleted_at')) {
                    $table->softDeletes();
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
        Schema::dropIfExists('permissions');
    }
};
