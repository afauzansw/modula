<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property int $instructor_id
 * @property int|null $category_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property int $price
 * @property bool $is_free
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'instructor_id',
    'category_id',
    'title',
    'slug',
    'description',
    'price',
    'is_free',
    'status',
])]
class Course extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_free' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->singleFile();
        $this->addMediaCollection('certificate_template')->singleFile();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<Module, $this>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    /**
     * @return HasManyThrough<Lesson, Module, $this>
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Module::class);
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<Rating, $this>
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * @return HasMany<Certificate, $this>
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
