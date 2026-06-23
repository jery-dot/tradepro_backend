<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Add this to see what's happening in storage/logs/laravel.log
        Log::info("Stripe Webhook Received: {$event->type}", ['id' => $event->id]);

        // Handle the event
        switch ($event->type) {
            case 'invoice.paid':
                $this->handleInvoicePaid($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleInvoicePaid($invoice)
    {
        $stripeSubscriptionId = $invoice->subscription;
        $subscription = UserSubscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();

        if ($subscription) {
            $subscription->update([
                'stripe_status' => 'active',
                // Extend the period based on Stripe's data
                'trial_ends_at' => now()->addMonth(), 
            ]);
        }
    }

    protected function handlePaymentFailed($invoice)
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $invoice->subscription)->first();
        if ($subscription) {
            $subscription->update(['stripe_status' => 'past_due']);
            // Here you could trigger an email to the user: "Your payment failed!"
        }
    }

    protected function handleSubscriptionDeleted($stripeSubscription)
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        if ($subscription) {
            $subscription->update(['stripe_status' => 'canceled']);
            
            // Clear the active pointer on the User model
            $user = User::find($subscription->user_id);
            if ($user) {
                $user->update(['active_subscription_id' => null]);
            }
        }
    }
}