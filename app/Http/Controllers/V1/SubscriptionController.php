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
        try {
            $user = $request->user();

            // Ensure Stripe Customer exists
            if (! $user->stripe_id) {
                $customer = \Stripe\Customer::create([
                    'email' => $user->email,
                    'name' => $user->name,
                ]);
                $user->update(['stripe_id' => $customer->id]);
            }

            // Create Ephemeral Key for Flutter
            $ephemeralKey = \Stripe\EphemeralKey::create(
                ['customer' => $user->stripe_id],
                ['stripe_version' => '2022-11-15']
            );

            return response()->json([
                'status' => true,
                'customer_id' => $user->stripe_id,
                'ephemeral_key' => $ephemeralKey->secret,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeSubscription(Request $request)
    {
        $user = $request->user();
        $plan = Plan::findOrFail($request->plan_id);

        // 1. Attach Payment Method to Customer
        $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method_id);
        $paymentMethod->attach(['customer' => $user->stripe_id]);

        // 2. Set as Default
        \Stripe\Customer::update($user->stripe_id, [
            'invoice_settings' => ['default_payment_method' => $paymentMethod->id],
        ]);

        // 3. Create Subscription (status will be 'incomplete')
        $subscription = \Stripe\Subscription::create([
            'customer' => $user->stripe_id,
            'items' => [['price_data' => [
                'unit_amount' => $plan->price * 100,
                'currency' => 'usd',
                'product' => $plan->stripe_product_id,
                'recurring' => ['interval' => 'month'],
            ]]],
            'payment_behavior' => 'default_incomplete',
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        // 4. Save to your local DB
        $userSub = UserSubscription::create([
            'user_id' => $user->id,
            'stripe_subscription_id' => $subscription->id,
            'stripe_status' => $subscription->status,
        ]);

        return response()->json([
            'payment_intent_client_secret' => $subscription->latest_invoice->payment_intent->client_secret,
        ]);
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
