<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $module_id
 * @property string $title
 * @property string $type
 * @property string|null $content
 * @property string|null $video_path
 * @property int|null $video_duration_seconds
 * @property int $order
 * @property bool $is_preview
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'module_id',
    'title',
    'type',
    'content',
    'video_path',
    'video_duration_seconds',
    'order',
    'is_preview',
])]
class Lesson extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'video_duration_seconds' => 'integer',
            'order' => 'integer',
            'is_preview' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * @return HasOne<Quiz, $this>
     */
    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    /**
     * @return HasOne<Assignment, $this>
     */
    public function assignment(): HasOne
    {
        return $this->hasOne(Assignment::class);
    }

    /**
     * @return HasMany<LessonProgress, $this>
     */
    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
}
