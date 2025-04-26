<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salary_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained()->onDelete('cascade');
            $table->decimal('salary', 10, 2)->nullable();
            $table->decimal('food_allowance', 10, 2)->nullable();
            $table->decimal('allowances', 10, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            // Add unique constraint to prevent duplicate calculations
            $table->unique(['artisan_id', 'start_date', 'end_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_calculations');
    }
};
