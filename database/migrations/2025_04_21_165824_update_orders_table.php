<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('product_photo')->nullable();
            $table->string('client_name');
            $table->dropColumn('client_details');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('product_photo');
            $table->dropColumn('client_name');
            $table->text('client_details')->nullable();
        });
    }
};
