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
                $table->string('group_name')->nullable();
                $table->timestamps();

                $table->index('name');
                $table->index('group_name');
            });
        } else {
            // Update existing table if needed
            Schema::table('permissions', function (Blueprint $table) {
                if (!Schema::hasColumn('permissions', 'group_name')) {
                    $table->string('group_name')->nullable()->after('name');
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
