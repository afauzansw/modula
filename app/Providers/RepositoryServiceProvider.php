<?php

namespace App\Providers;

use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\InstructorRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Eloquent\EloquentAuthRepository;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Repositories\Eloquent\EloquentCourseRepository;
use App\Repositories\Eloquent\EloquentInstructorRepository;
use App\Repositories\Eloquent\EloquentPaymentRepository;
use App\Repositories\Eloquent\EloquentRoleRepository;
use App\Repositories\Eloquent\EloquentStudentRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Repository interface → Eloquent implementation bindings.
     *
     * Phase 2 adds the domain repositories (Course, Module, Lesson, …) here.
     * BaseRepository / BaseRepositoryInterface are never bound directly — only
     * concrete subclasses are.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        AuthRepositoryInterface::class => EloquentAuthRepository::class,
        CategoryRepositoryInterface::class => EloquentCategoryRepository::class,
        CourseRepositoryInterface::class => EloquentCourseRepository::class,
        InstructorRepositoryInterface::class => EloquentInstructorRepository::class,
        PaymentRepositoryInterface::class => EloquentPaymentRepository::class,
        RoleRepositoryInterface::class => EloquentRoleRepository::class,
        StudentRepositoryInterface::class => EloquentStudentRepository::class,
    ];
}
