<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Google Books cover URLs routinely exceed 255 chars (they carry long
        // signed image tokens as query params), which overflowed the original
        // VARCHAR(255) column.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return; // SQLite has no fixed-length VARCHAR to widen.
        }

        DB::statement('ALTER TABLE products MODIFY cover_url TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE products MODIFY cover_url VARCHAR(255) NULL');
    }
};
