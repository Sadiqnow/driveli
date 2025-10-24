<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerificationRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verification_rules', function (Blueprint $table) {
            $table->id();
            $table->string('factor'); // e.g., 'ocr_accuracy', 'face_match', 'validation_consistency'
            $table->decimal('weight', 3, 2); // Weight as decimal (0.00 to 1.00)
            $table->json('rules')->nullable(); // JSON for additional rules like thresholds
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('verification_rules');
    }
}
