<?php

namespace App\Models;

use Database\Factories\RatingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $course_id
 * @property int $stars
 * @property string|null $review_text
 * @property int $progress_percent_at_review
 * @property int|null $last_lesson_id_at_review
 * @property int $edit_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'course_id',
    'stars',
    'review_text',
    'progress_percent_at_review',
    'last_lesson_id_at_review',
    'edit_count',
])]
class Rating extends Model
{
    /** @use HasFactory<RatingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stars' => 'integer',
            'progress_percent_at_review' => 'integer',
            'edit_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * The last lesson the student had reached when they first submitted this review.
     *
     * @return BelongsTo<Lesson, $this>
     */
    public function lastLessonAtReview(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'last_lesson_id_at_review');
    }
}
