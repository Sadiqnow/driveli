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
        Schema::create('data_migration_backup', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->json('original_data'); // Store the original record as JSON
            $table->string('migration_type'); // 'consolidation', 'cleanup', 'enhancement'
            $table->string('migration_step'); // Specific step in the migration process
            $table->timestamp('backed_up_at');
            $table->timestamps();

            $table->index(['table_name', 'migration_type']);
            $table->index('backed_up_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_migration_backup');
    }
};
