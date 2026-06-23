<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Job;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with('user')->latest();

        if ($request->filled('plan') && $request->plan !== 'all') {
            $query->where('plan_name', ucfirst($request->plan));
        }

        $subscriptions = $query->paginate(15)->withQueryString();

        $plans = [
            'labourer' => [
                'count' => Subscription::where('status','active')->where('plan_name','Labourer')->count(),
                'mrr'   => Subscription::where('status','active')->where('plan_name','Labourer')->sum('amount'),
            ],
            'contractor' => [
                'count' => Subscription::where('status','active')->where('plan_name','Contractor')->count(),
                'mrr'   => Subscription::where('status','active')->where('plan_name','Contractor')->sum('amount'),
            ],
            'apprentice' => [
                'count' => Subscription::where('status','active')->where('plan_name','Apprentice')->count(),
                'mrr'   => Subscription::where('status','active')->where('plan_name','Apprentice')->sum('amount'),
            ],
        ];

        $userCount = User::count();
        $jobCount  = Job::where('status','open')->count();

        return view('admin.subscriptions', compact('subscriptions','plans','userCount','jobCount'));
    }

    public function show(Subscription $sub)
    {
        return view('admin.subscription-show', compact('sub'));
    }

    public function cancel(Subscription $sub)
    {
        $sub->update([
            'status'          => 'cancelled',
            'cancelled_at'    => now(),
            'next_billing_at' => null,
        ]);
        return back()->with('status','Subscription cancelled.');
    }
}
