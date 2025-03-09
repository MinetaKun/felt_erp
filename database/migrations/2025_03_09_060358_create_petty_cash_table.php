<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_petty_cash_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePettyCashTable extends Migration
{
    public function up()
    {
        Schema::create('petty_cash', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('pan_bill')->nullable();
            $table->string('est_bill')->nullable();
            $table->enum('category', [
                'CASH ON HAND',
                'KITCHEN EXP',
                'WATER',
                'FUEL',
                'ELECTRICITY',
                'PRODUCTION SUPPLIES',
                'REPAIR AND MAINTENANCE',
                'FIXED ASSETS',
                'WAGES',
                'ADVANCE',
                'SALARY',
                'OFFICE EXP',
                'STATIONERY',
                'ADVANCE RETURN',
                'PERFORMANCE APPRAISAL',
                'ADVANCE AMOUNT',
                'CURRENT ASSETS'
            ]);
            $table->string('particular');
            $table->decimal('cash_in', 10, 2)->default(0.00);
            $table->decimal('cash_out', 10, 2)->default(0.00);
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('petty_cash');
    }
}
