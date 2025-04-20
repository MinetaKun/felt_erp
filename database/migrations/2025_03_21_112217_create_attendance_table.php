<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceTable extends Migration
{
    public function up()
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artisan_id');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->enum('status', ['Present', 'Absent', 'Late']);
            $table->timestamps();

            $table->foreign('artisan_id')->references('id')->on('artisans')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance');
    }
}
