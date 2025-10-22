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
                $table->unsignedBigInteger('driver_id');
                $table->string('document_type');
                $table->string('document_path')->nullable();
                $table->longText('file_content')->nullable(); // Binary storage for file content
                $table->string('verification_status')->default('pending')->index();
                $table->timestamp('verified_at')->nullable();
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->json('ocr_data')->nullable();
                $table->timestamps();

                $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
                $table->foreign('verified_by')->references('id')->on('admin_users');

                $table->index(['driver_id', 'document_type']);
                $table->index(['verification_status', 'created_at']);
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
