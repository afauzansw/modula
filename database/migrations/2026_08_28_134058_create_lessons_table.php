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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['video', 'text', 'quiz', 'assignment'])->default('text');
            $table->longText('content')->nullable();
            $table->string('video_path')->nullable();
            $table->unsignedInteger('video_duration_seconds')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_preview')->default(false);
            $table->timestamps();

            $table->index(['module_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
