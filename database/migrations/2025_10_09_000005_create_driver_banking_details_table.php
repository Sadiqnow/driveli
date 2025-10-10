<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverBankingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('driver_banking_details')) {
            Schema::create('driver_banking_details', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('driver_id')->index();
                $table->unsignedBigInteger('bank_id')->nullable();
                $table->string('account_name')->nullable();
                $table->string('account_number')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->boolean('is_primary')->default(false)->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('driver_banking_details')) {
            Schema::dropIfExists('driver_banking_details');
        }
    }
}
