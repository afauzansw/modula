<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentRepositoryInterface $payments) {}

    public function index(): Response
    {
        return Inertia::render('admin/payments/index');
    }

    public function fetch(): JsonResponse
    {
        $payments = $this->payments->all();

        $rows = [];

        foreach ($payments->items() as $payment) {
            if (! $payment instanceof Payment) {
                continue;
            }

            $rows[] = [
                'id' => $payment->id,
                'student' => $payment->order->user->name,
                'course' => $payment->order->course->title,
                'order_number' => $payment->order->order_number,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'paid_at' => $payment->paid_at?->toIso8601String(),
            ];
        }

        return $this->paginatedJson($payments, $rows);
    }
}
