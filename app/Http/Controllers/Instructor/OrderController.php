<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('instructor/orders');
    }
}
