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
        Schema::table('driver_company_relations', function (Blueprint $table) {
            $table->timestamp('last_reminder_sent_at')->nullable()->after('feedback_submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_company_relations', function (Blueprint $table) {
            $table->dropColumn('last_reminder_sent_at');
        });
    }
};
