<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('finished_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // e.g., cat cave, felt mat, etc.
            $table->string('color');
            $table->string('size');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->string('location')->nullable(); // storage location
            $table->string('status')->default('available'); // available, reserved, sold
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('finished_products');
    }
};
