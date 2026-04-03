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
        Schema::create('students', function (Blueprint $col) {
            $col->id();
            $col->string('nis')->unique();
            $col->string('name');
            $col->string('class');
            $col->string('qr_token')->unique();
            $col->string('photo')->nullable();
            $col->text('face_descriptor')->nullable(); // JSON data for face metrics
            $col->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
