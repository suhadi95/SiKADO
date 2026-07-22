<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('activity_files')
            ->where('type', 'undangan')
            ->update(['type' => 'dasar']);
    }

    public function down(): void
    {
        DB::table('activity_files')
            ->where('type', 'dasar')
            ->update(['type' => 'undangan']);
    }
};
