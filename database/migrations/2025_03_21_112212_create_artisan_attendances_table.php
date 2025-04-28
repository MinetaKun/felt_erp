<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artisan_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['present', 'absent', 'late'])->default('present');
            $table->date('date');
            $table->timestamps();

            $table->unique(['artisan_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artisan_attendances');
    }
};
