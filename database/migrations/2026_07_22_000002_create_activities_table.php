<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->date('activity_date');
            $table->string('name');
            $table->boolean('requires_evidence')->default(true);
            $table->text('notes')->nullable();
            $table->string('drive_folder_id')->nullable();
            $table->string('drive_folder_url')->nullable();
            $table->boolean('drive_sync_failed')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['activity_date', 'category_id']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
