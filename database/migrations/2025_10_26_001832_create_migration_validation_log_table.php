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
        Schema::create('migration_validation_log', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->string('validation_type'); // 'data_integrity', 'foreign_key', 'constraint', 'custom'
            $table->string('validation_rule');
            $table->integer('records_checked');
            $table->integer('records_passed');
            $table->integer('records_failed');
            $table->json('failed_records')->nullable(); // IDs or details of failed records
            $table->text('error_message')->nullable();
            $table->enum('status', ['passed', 'failed', 'warning'])->default('passed');
            $table->timestamp('validated_at');
            $table->timestamps();

            $table->index(['table_name', 'validation_type']);
            $table->index(['status', 'validated_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('migration_validation_log');
    }
};
