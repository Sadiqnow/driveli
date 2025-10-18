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
        Schema::table('permissions', function (Blueprint $table) {
            // Drop custom columns that are not part of Spatie package
            $table->dropColumn([
                'display_name',
                'description',
                'category',
                'resource',
                'action',
                'is_active',
                'meta',
                'deleted_at'
            ]);

            // Ensure guard_name is not nullable (Spatie requirement)
            $table->string('guard_name')->nullable(false)->default('web')->change();

            // Add unique constraint on name and guard_name (Spatie requirement)
            $table->unique(['name', 'guard_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique(['name', 'guard_name']);

            // Make guard_name nullable again
            $table->string('guard_name')->nullable()->change();

            // Add back the custom columns
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('resource')->nullable();
            $table->string('action')->nullable();
            $table->boolean('is_active')->default(1);
            $table->longText('meta')->nullable();
            $table->softDeletes();
        });
    }
};
