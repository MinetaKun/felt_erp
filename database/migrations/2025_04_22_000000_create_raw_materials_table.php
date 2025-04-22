<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // e.g., wool, dye, etc.
            $table->string('color')->nullable();
            $table->decimal('quantity', 10, 2); // in kg or units
            $table->string('unit'); // kg, meters, etc.
            $table->decimal('min_stock_level', 10, 2); // threshold for low stock alerts
            $table->decimal('price_per_unit', 10, 2);
            $table->string('supplier')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable(); // storage location
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('raw_materials');
    }
};
