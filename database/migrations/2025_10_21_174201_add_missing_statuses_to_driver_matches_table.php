<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the status enum to include missing values
        DB::statement("ALTER TABLE driver_matches MODIFY COLUMN status ENUM('pending', 'matched', 'accepted', 'declined', 'completed', 'cancelled', 'failed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE driver_matches MODIFY COLUMN status ENUM('pending', 'accepted', 'declined', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
