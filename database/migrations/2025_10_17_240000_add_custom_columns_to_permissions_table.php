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
            $table->string('display_name')->nullable()->after('name');
            $table->text('description')->nullable()->after('display_name');
            $table->string('category')->nullable()->after('description');
            $table->string('resource')->nullable()->after('category');
            $table->string('action')->nullable()->after('resource');
            $table->boolean('is_active')->default(1)->after('action');
            $table->longText('meta')->nullable()->after('is_active');
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
            $table->dropColumn([
                'display_name',
                'description',
                'category',
                'resource',
                'action',
                'is_active',
                'meta'
            ]);
        });
    }
};
