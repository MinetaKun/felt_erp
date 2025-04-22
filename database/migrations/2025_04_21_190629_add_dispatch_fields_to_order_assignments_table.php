<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_assignments', function (Blueprint $table) {
            $table->date('dispatch_date')->nullable()->after('dispatched_by');
            $table->string('dispatch_method')->nullable()->after('dispatch_date');
            $table->text('dispatch_notes')->nullable()->after('dispatch_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_assignments', function (Blueprint $table) {
            $table->dropColumn('dispatch_date');
            $table->dropColumn('dispatch_method');
            $table->dropColumn('dispatch_notes');
        });
    }
};
