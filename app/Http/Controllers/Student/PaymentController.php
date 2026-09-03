<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(private readonly OrderRepositoryInterface $orders) {}

    public function index(Request $request): Response
    {
        $orders = $this->orders
            ->forStudent($request->user()->id)
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'course' => $order->course->title,
                'order_number' => $order->order_number,
                'amount' => $order->amount,
                'method' => $order->payments->first()?->method,
                'status' => $order->status,
                'paid_at' => $order->paid_at?->toIso8601String(),
            ]);

        return Inertia::render('student/payments', ['orders' => $orders]);
    }
}
