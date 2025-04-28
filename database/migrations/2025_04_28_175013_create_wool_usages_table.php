<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wool_usages', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('bill_number', 50);
            $table->string('department', 100);
            $table->string('color_number', 50);
            $table->integer('roll_count');
            $table->decimal('total_kg', 10, 2);
            $table->string('remarks')->nullable();
            $table->string('usage_purpose', 100);
            $table->foreignId('supplier_id')->constrained('wool_suppliers');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wool_usages');
    }
};
