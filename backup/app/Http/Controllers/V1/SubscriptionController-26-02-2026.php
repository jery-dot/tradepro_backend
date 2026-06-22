<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
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
        $user = $request->user();

        // 1. Create Stripe Customer if not exists
        if (! $user->stripe_id) {
            $customer = \Stripe\Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $user->update(['stripe_id' => $customer->id]);
        }

        // 2. Create a SetupIntent
        // This allows Flutter to securely collect card info
        $setupIntent = \Stripe\SetupIntent::create([
            'customer' => $user->stripe_id,
            'payment_method_types' => ['card'],
        ]);

        // 3. Create Ephemeral Key for the customer
        $ephemeralKey = \Stripe\EphemeralKey::create(
            ['customer' => $user->stripe_id],
            ['stripe_version' => '2022-11-15'] // Use your Stripe API version
        );

        return response()->json([
            'intent_client_secret' => $setupIntent->client_secret,
            'customer_id' => $user->stripe_id,
            'ephemeral_key' => $ephemeralKey->secret, // Add this
        ]);
    }

    /**
     * Summary of storeSubscription
     */
    public function storeSubscription(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method_id' => 'required|string',
        ]);

        $user = $request->user();
        $plan = Plan::find($request->plan_id);

        try {
            // 1. Ensure User has a Stripe ID
            if (! $user->stripe_id) {
                $customer = \Stripe\Customer::create(['email' => $user->email]);
                $user->update(['stripe_id' => $customer->id]);
            }
            // return response()->json(['message' => 'Stripe customer ready', 'stripe_id' => $user->stripe_id]);

            // 2. Attach the Payment Method
            // We use the returned object to ensure we have the latest state
            $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method_id);

            if ($paymentMethod->customer !== $user->stripe_id) {
                $paymentMethod = $paymentMethod->attach(['customer' => $user->stripe_id]);
            }
            // return response()->json(['message' => 'Payment method attached', 'payment_method_id' => $paymentMethod->id]);

            // 3. Update Customer default
            \Stripe\Customer::update($user->stripe_id, [
                'invoice_settings' => ['default_payment_method' => $paymentMethod->id],
            ]);

            // return response()->json(['message' => 'Customer payment method updated', 'customer'=> \Stripe\Customer::retrieve($user->stripe_id)]);

            // 4. Create Subscription
            $subscription = \Stripe\Subscription::create([
                'customer' => $user->stripe_id,
                'items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product' => $plan->stripe_price_id, // You still need a Product ID
                        'unit_amount' => (int)$plan->price,          // Amount in cents ($20.00)
                        'recurring' => ['interval' => 'month'],
                    ],
                ]],
                'trial_period_days' => $plan->trial_days,
                // Use payment_behavior to handle incomplete payments (SCA/3DS)
                'payment_behavior' => 'default_incomplete',
                'default_payment_method' => $paymentMethod->id,
                'expand' => [
                    'latest_invoice.payment_intent'
                ],
            ]);


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

    if (!$sub) {
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
        'trial_ends_at' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end)
    ]);

    return response()->json([
        'message' => 'Subscription will cancel at the end of the billing period.',
        'ends_at' => $sub->trial_ends_at->format('M d, Y')
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
