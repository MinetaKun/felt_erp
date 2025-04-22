<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wool_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wool_order_id')->constrained()->onDelete('cascade');
            $table->string('wool_type');
            $table->string('color');
            $table->decimal('quantity', 10, 2);
            $table->string('unit'); // kg, lbs, etc.
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->text('specifications')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wool_order_items');
    }
};
