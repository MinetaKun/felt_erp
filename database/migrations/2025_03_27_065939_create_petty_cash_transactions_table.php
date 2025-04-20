<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('petty_cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->date('bs_date')->nullable();
            $table->string('pan_bill_no')->nullable();
            $table->string('est_bill_no')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('particulars');
            $table->decimal('cash_in', 15, 2)->default(0);
            $table->decimal('cash_out', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('vat_percentage', 5, 2)->nullable();
            $table->boolean('is_vat_included')->default(false);
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('category_id')
                ->references('id')
                ->on('petty_cash_categories')
                ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('petty_cash_transactions');
    }
};
