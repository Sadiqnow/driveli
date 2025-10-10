<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('driver_documents')) {
            Schema::create('driver_documents', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('driver_id')->index();
                $table->string('document_type')->nullable();
                $table->string('document_path')->nullable();
                $table->longText('file_content')->nullable(); // Binary storage for file content
                $table->string('verification_status')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('driver_documents')) {
            Schema::dropIfExists('driver_documents');
        }
    }
}
