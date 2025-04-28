<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('product_name');
            $table->string('size')->nullable();
            $table->string('wool_color')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->integer('total_quantity');
            $table->date('due_date');
            $table->enum('status', ['pending', 'in_production', 'approved', 'dispatched'])->default('pending');
            $table->decimal('wages_per_unit', 8, 2);
            $table->text('client_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
