<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('drive_file_id')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['activity_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_files');
    }
};
