<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archives', function (Blueprint $col) {
            $col->id();
            $col->string('filename');
            $col->string('type')->default('daily'); // daily, monthly
            $col->string('period_label'); // e.g. "2 April 2026" or "April 2026"
            $col->integer('total_records')->default(0);
            $col->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
