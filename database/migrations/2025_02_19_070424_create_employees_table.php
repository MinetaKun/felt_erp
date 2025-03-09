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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('employee_type', ['artisan', 'accountant', 'quality_checker', 'manager', 'supervisor', 'driver', 'cleaner', 'security_guard', 'other']);
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->date('join_date');
            $table->integer('mobile')->nullable();
            $table->json('skills')->nullable();
            $table->string('citizenship_no')->nullable();
            $table->string('citizenship_photo')->nullable();
            $table->string('pan_no')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
