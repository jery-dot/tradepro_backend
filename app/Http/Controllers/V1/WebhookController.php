<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            // Verify the signature to ensure the request is from Stripe
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event types
        switch ($event->type) {
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleSubscriptionUpdated($subscription)
    {
        UserSubscription::where('stripe_subscription_id', $subscription->id)
            ->update([
                'stripe_status' => $subscription->status,
                'trial_ends_at' => $subscription->trial_end ? now()->setTimestamp($subscription->trial_end) : null,
                'ends_at' => $subscription->cancel_at ? now()->setTimestamp($subscription->cancel_at) : null,
            ]);
    }

    protected function handleSubscriptionDeleted($subscription)
    {
        UserSubscription::where('stripe_subscription_id', $subscription->id)
            ->update([
                'stripe_status' => 'canceled',
                'ends_at' => now(),
            ]);
    }

    protected function handlePaymentSucceeded($invoice)
    {
        // This is where you could log a "Transaction" or send a receipt email
        if ($invoice->subscription) {
            UserSubscription::where('stripe_subscription_id', $invoice->subscription)
                ->update(['stripe_status' => 'active']);
        }
    }

    protected function handlePaymentFailed($invoice)
    {
        // Mark the subscription as past_due so the Flutter app can show a warning
        if ($invoice->subscription) {
            UserSubscription::where('stripe_subscription_id', $invoice->subscription)
                ->update(['stripe_status' => 'past_due']);
        }
    }
}
