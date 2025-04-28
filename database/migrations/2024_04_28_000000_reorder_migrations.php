<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ReorderMigrations extends Migration
{
    public function up()
    {
        // This migration ensures proper ordering of migrations
        // The actual reordering will be done by renaming the files
    }

    public function down()
    {
        // No down migration needed
    }
}
