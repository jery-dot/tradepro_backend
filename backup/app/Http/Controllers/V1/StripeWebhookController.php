<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $event = \Stripe\Webhook::constructEvent(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            config('services.stripe.webhook_secret')
        );

        switch ($event->type) {

            case 'invoice.payment_succeeded':
                $data = $event->data->object;

                UserSubscription::where(
                    'stripe_subscription_id',
                    $data->subscription
                )->update([
                    'stripe_status' => 'active'
                ]);
                break;

            case 'invoice.payment_failed':
                $data = $event->data->object;

                UserSubscription::where(
                    'stripe_subscription_id',
                    $data->subscription
                )->update([
                    'stripe_status' => 'past_due'
                ]);
                break;

            case 'customer.subscription.deleted':
                $sub = $event->data->object;

                UserSubscription::where(
                    'stripe_subscription_id',
                    $sub->id
                )->update([
                    'stripe_status' => 'cancelled',
                    'ends_at' => now()
                ]);
                break;
        }

        return response()->json(['status' => 'ok']);
    }
}
