<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE team_members MODIFY COLUMN role ENUM('leader','admin','editor','member') NOT NULL DEFAULT 'member'");
    }

    public function down(): void
    {
        DB::statement("UPDATE team_members SET role = 'member' WHERE role IN ('admin','editor')");
        DB::statement("ALTER TABLE team_members MODIFY COLUMN role ENUM('leader','member') NOT NULL DEFAULT 'member'");
    }
};
