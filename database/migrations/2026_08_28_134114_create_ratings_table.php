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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('stars');
            $table->text('review_text')->nullable();

            // Snapshot of the student's progress at the time of the FIRST submission.
            // These are frozen: never rewritten when the rating is later edited.
            $table->unsignedTinyInteger('progress_percent_at_review');
            $table->foreignId('last_lesson_id_at_review')->nullable()->constrained('lessons')->nullOnDelete();

            $table->unsignedTinyInteger('edit_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
            $table->index('course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
