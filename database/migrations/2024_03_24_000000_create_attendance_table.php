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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->morphs('attendanceable'); // Creates attendanceable_id and attendanceable_type columns
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->enum('status', ['present', 'absent', 'late'])->default('present');
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Add indexes for better query performance
            $table->index(['date', 'status']);
            $table->index(['attendanceable_id', 'attendanceable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
