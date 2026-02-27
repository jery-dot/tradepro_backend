<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        // Use config() instead of env() for stability
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Summary of prepareSubscription
     */
    
    public function prepareSubscription(Request $request)
    {
        try {

            $user = $request->user();

            // 1. Create Stripe Customer if not exists
            if (! $user->stripe_id) {
                $customer = \Stripe\Customer::create([
                    'email' => $user->email,
                    'name' => $user->name,
                ]);
                $user->update(['stripe_id' => $customer->id]);
            }

            $request->validate([
                'amount' => 'required|numeric|min:1',
            ]);

            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount, // smallest currency unit
                'currency' => 'usd',
                'customer' => $user->stripe_id,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return response()->json([
                'status' => true,
                'intent_client_secret' => $paymentIntent->client_secret,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeSubscription(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method_id' => 'required|string',
        ]);

        $paymentMethod = $request->payment_method_id;

        $plan = Plan::find($request->plan_id);

        try {
            // 1. Ensure User has a Stripe ID
            if (! $user->stripe_id) {
                $customer = \Stripe\Customer::create(['email' => $user->email]);
                $user->update(['stripe_id' => $customer->id]);
            }

            //get the user stripe ID
            $customerId = $user->stripe_id;

            // ⭐ Attach payment method
            // We use the returned object to ensure we have the latest state
            $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method_id);

            if ($paymentMethod->customer !== $user->stripe_id) {
                $paymentMethod = $paymentMethod->attach(['customer' => $user->stripe_id]);
            }


            // ⭐ Set default payment method
            \Stripe\Customer::update($customerId, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethod,
                ],
            ]);


            // ⭐ Create subscription
            $subscription = \Stripe\Subscription::create([
                'customer' => $customerId,
                'items' => [[
                    'price_data' => [
                        'unit_amount' => $plan->price * 100, // Convert $19.99 to 1999 cents
                        'currency' => 'usd',
                        'product' => $plan->stripe_price_id, // Your prod_xxx ID goes here
                        'recurring' => ['interval' => 'month'],
                    ],
                ]],
                'trial_period_days' => $plan->trial_days,
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            // return response()->json(['message' => 'Stripe customer ready', 'stripe_id' => $user->stripe_id]);

            // 5. Save to Database
            $userSub = UserSubscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'stripe_subscription_id' => $subscription->id,
                'stripe_status' => $subscription->status,
                'stripe_price_id' => $plan->stripe_price_id,
                'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
            ]);

            // Update User's active pointer
            $user->update(['active_subscription_id' => $userSub->id]);

            return response()->json([
                'message' => 'Subscription successful',
                'subscription' => $userSub,
                // Add these two lines for Flutter to handle 3D Secure
                'status' => $subscription->status,
                'payment_intent_client_secret' => $subscription->latest_invoice->payment_intent->client_secret ?? null,
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Specifically catch Stripe errors for better debugging
            return response()->json(['error' => $e->getMessage()], 402);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Summary of cancel
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        $sub = $user->activeSubscription;

        if (! $sub) {
            return response()->json(['error' => 'No active subscription found'], 404);
        }

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        // 1. Update Stripe
        $stripeSubscription = $stripe->subscriptions->update($sub->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);

        // 2. Update local DB
        // We store 'cancelling' so the UI can show a different "Ending soon" state
        $sub->update([
            'stripe_status' => 'cancelling',
            'trial_ends_at' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
        ]);

        return response()->json([
            'message' => 'Subscription will cancel at the end of the billing period.',
            'ends_at' => $sub->trial_ends_at->format('M d, Y'),
        ]);
    }

    public function cancelOld(Request $request)
    {
        $user = $request->user();
        $sub = $user->activeSubscription;

        if (! $sub) {
            return response()->json(['error' => 'No active subscription found'], 404);
        }

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        // Tell Stripe not to renew at the end of the cycle
        $stripe->subscriptions->update($sub->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);

        // Update local DB status
        $sub->update(['stripe_status' => 'cancelling']);

        return response()->json(['message' => 'Subscription will cancel at the end of the billing period.']);
    }

    public function status(Request $request)
    {
        $user = $request->user()->load('activeSubscription.plan');

        return response()->json([
            'is_subscribed' => $user->isSubscribed(),
            'subscription' => $user->activeSubscription,
            // Send features so Flutter can enable/disable buttons dynamically
            'features' => $user->activeSubscription ? $user->activeSubscription->plan->features : [],
        ]);
    }

    public function getPortalUrl(Request $request)
    {
        $user = $request->user();

        if (! $user->stripe_id) {
            return response()->json(['error' => 'No stripe customer found'], 404);
        }

        // This creates a secure link that expires after use
        $session = \Stripe\BillingPortal\Session::create([
            'customer' => $user->stripe_id,
            'return_url' => 'http://yourapp.com/home', // Where to go when they click 'Back'
        ]);

        return response()->json(['url' => $session->url]);
    }
}
