<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify existing tables to add missing columns

        // Add missing columns to roles table
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

        // Add missing columns to permissions table
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
            if (!Schema::hasColumn('permissions', 'group_name')) {
                $table->string('group_name')->nullable()->after('category');
            }
            if (!Schema::hasColumn('permissions', 'resource')) {
                $table->string('resource')->nullable()->after('group_name');
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

        // Create role_permissions table if it doesn't exist, or modify if it does
        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('permission_id');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

                $table->unique(['role_id', 'permission_id']);
                $table->index(['role_id', 'is_active']);
                $table->index(['permission_id', 'is_active']);
            });
        } else {
            Schema::table('role_permissions', function (Blueprint $table) {
                if (!Schema::hasColumn('role_permissions', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('permission_id');
                }
            });
        }

        // Add missing columns to user_roles table
        if (Schema::hasTable('user_roles')) {
            Schema::table('user_roles', function (Blueprint $table) {
                if (!Schema::hasColumn('user_roles', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('role_id');
                }
                if (!Schema::hasColumn('user_roles', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->after('is_active');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
