<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use App\Models\Plan;

class StripeController extends Controller
{
    public function checkout(Request $request, $log_id)
    {
    }
    public function session(Request $request, $log_id)
    {
    }
    public function success(Request $request, $log_id)
    {
    }
    public function cancel(Request $request, $log_id)
    {
    }
}
