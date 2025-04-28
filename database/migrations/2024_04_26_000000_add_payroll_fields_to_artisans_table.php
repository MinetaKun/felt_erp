<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('artisans', function (Blueprint $table) {
            $table->string('bank_account_number')->nullable()->after('basic_salary');
            $table->boolean('is_production_based')->default(false)->after('bank_account_number');
        });
    }

    public function down()
    {
        Schema::table('artisans', function (Blueprint $table) {
            $table->dropColumn(['bank_account_number', 'is_production_based']);
        });
    }
};
