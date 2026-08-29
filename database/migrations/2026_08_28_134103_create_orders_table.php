<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->string('gateway_ref')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('course_id');
            $table->index('status');
        });

        /*
         * Enforce "at most one active order per (user, course)" at the database level.
         * A PostgreSQL partial unique index: only pending/paid orders participate, so
         * failed/expired orders never block a fresh checkout. This is the race-condition
         * backstop; the checkout flow still validates in PHP for a friendlier message.
         *
         * Raw SQL because the fluent schema builder has no partial-index method.
         */
        DB::statement(
            "CREATE UNIQUE INDEX orders_one_active_per_course ON orders (user_id, course_id) WHERE status IN ('pending', 'paid')"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
