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
        // Using raw SQL for enum modification as it is more reliable across different MySQL versions/Laravel setups
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('Hadir', 'Terlambat', 'Alfa', 'Sakit', 'Izin') DEFAULT 'Alfa'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('Hadir', 'Terlambat', 'Alfa') DEFAULT 'Alfa'");
    }
};
