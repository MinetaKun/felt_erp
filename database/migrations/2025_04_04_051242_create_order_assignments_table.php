<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('artisan_id')->constrained()->onDelete('cascade');
            $table->integer('assigned_quantity');
            $table->integer('completed_quantity')->default(0);
            $table->integer('approved_quantity')->default(0);
            $table->integer('rejected_quantity')->default(0);
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('dispatched_at')->nullable();
            $table->foreignId('dispatched_by')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'in_production', 'completed', 'approved', 'dispatched'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_assignments');
    }
};
