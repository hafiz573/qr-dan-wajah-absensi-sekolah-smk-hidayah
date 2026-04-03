<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $col) {
            $col->id();
            $col->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $col->date('date');
            $col->time('time');
            $col->enum('status', ['Hadir', 'Terlambat', 'Alfa'])->default('Alfa');
            $col->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
