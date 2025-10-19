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
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name')->nullable();
                $table->text('description')->nullable();
                $table->integer('level')->default(1);
                $table->boolean('is_active')->default(true);
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('name');
                $table->index(['name', 'is_active']);
                $table->index('level');
            });
        } else {
            // Update existing table if needed
            Schema::table('roles', function (Blueprint $table) {
                if (!Schema::hasColumn('roles', 'display_name')) {
                    $table->string('display_name')->nullable()->after('name');
                }
                if (!Schema::hasColumn('roles', 'description')) {
                    $table->text('description')->nullable()->after('display_name');
                }
                if (!Schema::hasColumn('roles', 'level')) {
                    $table->integer('level')->default(1)->after('description');
                }
                if (!Schema::hasColumn('roles', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('level');
                }
                if (!Schema::hasColumn('roles', 'meta')) {
                    $table->json('meta')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('roles', 'deleted_at')) {
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
        Schema::dropIfExists('roles');
    }
};
