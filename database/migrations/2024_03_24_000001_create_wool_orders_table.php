<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wool_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wool_supplier_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->date('expected_delivery_date');
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending'); // pending, confirmed, delivered, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wool_orders');
    }
};
