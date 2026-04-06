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
        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('type', ['Masuk', 'Pulang'])->default('Masuk')->after('time');
        });

        // Use raw SQL to update the status enum to include new status values
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('Hadir', 'Terlambat', 'Alfa', 'Sakit', 'Izin', 'Keluar', 'Keluar (Tanpa Scan Pagi)') DEFAULT 'Alfa'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('Hadir', 'Terlambat', 'Alfa', 'Sakit', 'Izin') DEFAULT 'Alfa'");
    }
};
