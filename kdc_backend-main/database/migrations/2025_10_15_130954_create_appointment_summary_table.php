<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_summary', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id');
            $table->longText('summary')->nullable();
            $table->unsignedBigInteger('treatment_stage_id')->nullable();
            $table->timestamps();
        });

        // Migrate existing appointment data
        DB::statement("
            INSERT INTO `appointment_summary` (appointment_id, summary, created_at, updated_at)
            SELECT 
                id,
                CONCAT(
                    'Status: ', appointment_status,
                    CASE WHEN inprogress_date IS NOT NULL THEN CONCAT('\nIn Progress: ', inprogress_date) ELSE '' END,
                    CASE WHEN complete_date IS NOT NULL THEN CONCAT('\nCompleted: ', complete_date) ELSE '' END
                ),
                created_at,
                updated_at
            FROM `appointments`
            WHERE appointment_status IN ('Inprogress', 'Complete', 'Completed', 'Rescheduled', 'Next')
            AND id NOT IN (SELECT appointment_id FROM `appointment_summary`)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_summary');
    }
};
