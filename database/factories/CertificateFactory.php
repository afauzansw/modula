<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'certificate_number' => 'CERT-'.now()->year.'-'.Str::upper(Str::random(8)),
            'file_path' => 'certificates/'.Str::lower(Str::random(16)).'.pdf',
            'issued_at' => now(),
        ];
    }
}
