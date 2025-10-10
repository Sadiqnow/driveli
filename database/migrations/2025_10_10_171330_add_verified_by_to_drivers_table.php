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
        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->foreign('verified_by')->references('id')->on('admin_users')->onDelete('set null');
            }
            if (!Schema::hasColumn('drivers', 'verification_notes')) {
                $table->text('verification_notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'verified_by')) {
                $table->dropForeign(['verified_by']);
                $table->dropColumn('verified_by');
            }
            if (Schema::hasColumn('drivers', 'verification_notes')) {
                $table->dropColumn('verification_notes');
            }
        });
    }
};
