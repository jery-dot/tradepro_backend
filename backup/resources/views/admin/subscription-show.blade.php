@extends('layouts.admin')
@section('page_title', 'Subscription Details')
@section('content')

@php
    // Safe extraction fallback parameters 
    $userTypeEnum = $subscription->user?->user_type;
    $firstName    = $subscription->user?->name ?? 'User';
    $lastName     = $subscription->user ? "" : 'Deleted';
    $userEmail    = $subscription->user?->email ?? '—';

    // Resolve structural color badges and string values from parameters
    $typeBadge   = $userTypeEnum ? $userTypeEnum->badgeClass() : 'bgr';
    $typeLabel   = $userTypeEnum ? $userTypeEnum->label() : 'Unknown';
    
    $planBadge   = ['Contractor' => 'bo', 'Labourer' => 'bn', 'Apprentice' => 'bp'][$subscription->plan_name] ?? 'bgr';
    
    $statusVal   = is_string($subscription->status) ? $subscription->status : (string)($subscription->status?->value ?? $subscription->status);
    $statusBadge = ['active' => 'bg', 'cancelled' => 'br', 'trial' => 'ba', 'expired' => 'bgr'][$statusVal] ?? 'bgr';
@endphp

<div class="ph">
    <div class="bc">
        Home <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <a href="{{ route('admin.subscriptions') }}" style="color:var(--grey)">Subscriptions</a>
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Details — {{ $firstName }}</span>
    </div>
    <div class="ph-row">
        <div>
            <div class="pt">Subscription Management</div>
            <div class="ps">Stripe Reference ID: <code style="font-family: monospace; font-size: 12px; color: var(--navy);">{{ $subscription->stripe_id ?? $subscription->id }}</code></div>
        </div>
        <a href="{{ route('admin.subscriptions') }}" class="btn btn-ol btn-sm">← Back to List</a>
    </div>
</div>

<div class="g2" style="align-items:start">

    {{-- Left Main Card: Subscription details --}}
    <div class="card">
        <div class="ch"><div class="ct">Plan Details</div></div>
        <div class="cb">
            <div class="dpav-w" style="text-align: center; margin-bottom: 20px;">
                <div style="font-size:48px; margin-bottom:8px; line-height: 1;">💳</div>
                <div class="dpn" style="font-size: 18px; font-weight: 600; color: var(--navy);">
                    {{ $subscription->plan->name ?? $subscription->plan_name }}
                </div>
                <div class="dpr" style="color: var(--grey); font-size: 13px; margin-top: 2px;">
                    Assigned to {{ $firstName }} {{ $lastName }}
                </div>
                <div style="display:flex; gap:6px; margin-top:12px; flex-wrap:wrap; justify-content:center">
                    <span class="bdg {{ $statusBadge }}">{{ ucfirst($statusVal) }}</span>
                    <span class="bdg {{ $planBadge }}">Plan rate</span>
                </div>
            </div>

            <div class="dp-sec">Financial Overview</div>
            <div class="dp-row">
                <span class="dpk">Recurring Price</span>
                <span class="dpv" style="color:var(--navy); font-weight:700">
                    {{ ($subscription->plan && $subscription->plan->price > 0) ? '$'.number_format($subscription->plan->price, 2) : 'Free' }}
                </span>
            </div>
            <div class="dp-row">
                <span class="dpk">Billing Period</span>
                <span class="dpv">{{ ucfirst($subscription->billing_cycle ?? 'Monthly') }}</span>
            </div>

            <div class="dp-sec">Lifecycle Timeline</div>
            <div class="dp-row">
                <span class="dpk">Subscription Started</span>
                <span class="dpv">
                    {{ $subscription->created_at ? \Illuminate\Support\Carbon::parse((string)$subscription->created_at)->format('d M Y (H:i)') : '—' }}
                </span>
            </div>
            <div class="dp-row">
                <span class="dpk">Next Billing Term</span>
                <span class="dpv" style="font-weight: 600;">
                    {{ $subscription->ends_at ? \Carbon\Carbon::parse($subscription->ends_at)->format('d M Y') : '—' }}
                </span>
            </div>
            @if($subscription->trial_ends_at)
                <div class="dp-row">
                    <span class="dpk">Trial Expiration</span>
                    <span class="dpv" style="color:var(--teal)">
                        {{ \Carbon\Carbon::parse($subscription->trial_ends_at)->format('d M Y') }}
                    </span>
                </div>
            @endif

            <div class="dp-sec">Subscriber Profiling</div>
            <div class="dp-row"><span class="dpk">Account Holder</span><span class="dpv">{{ $firstName }} {{ $lastName }}</span></div>
            <div class="dp-row"><span class="dpk">Account Email</span><span class="dpv">{{ $userEmail }}</span></div>
            <div class="dp-row">
                <span class="dpk">Platform Access</span>
                <span class="dpv">
                    <span class="bdg {{ $typeBadge }}">{{ $typeLabel }}</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Right Card: Admin management contextual workflows --}}
    <div class="card">
        <div class="ch"><div class="ct">Administrative Controls</div></div>
        <div class="cb" style="display:flex; flex-direction:column; gap:12px">
            
            @if($statusVal === 'active')
                <div style="font-size: 12px; color: var(--grey); background: rgba(239, 68, 68, 0.05); padding: 10px; border-radius: 6px; border: 1px dashed rgba(239, 68, 68, 0.2)">
                    <strong>Caution:</strong> Revoking access will immediately switch off premium gateway routing capabilities on the merchant terminal server.
                </div>
                <form method="POST" action="{{ route('admin.subscriptions.cancel', $subscription->id) }}"
                      onsubmit="return confirm('Cancel premium subscription for {{ $firstName }}?')">
                    @csrf 
                    @method('PATCH')
                    <button type="submit" class="btn btn-dn" style="width:100%; font-weight: 600;">
                        Term and Cancel Subscription
                    </button>
                </form>
            @else
                <div style="font-size: 12px; color: var(--grey); text-align:center; padding: 15px 0;">
                    🚫 No actions available. This subscription timeline status is currently structured as <strong>{{ $statusVal }}</strong>.
                </div>
            @endif

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 5px 0;">
            
            {{-- Platform Performance Micro Widget Stats --}}
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--grey); margin-bottom: -4px;">
                System Globals Overview
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; text-align: center;">
                <div style="background: #f8fafc; padding: 8px; border-radius: 4px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 14px; font-weight: 700; color: var(--navy)">{{ $userCount }}</div>
                    <div style="font-size: 9px; color: var(--grey)">Total Users</div>
                </div>
                <div style="background: #f8fafc; padding: 8px; border-radius: 4px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 14px; font-weight: 700; color: var(--orange)">{{ $jobCount }}</div>
                    <div style="font-size: 9px; color: var(--grey)">Pending Jobs</div>
                </div>
                <div style="background: #f8fafc; padding: 8px; border-radius: 4px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 14px; font-weight: 700; color: var(--purple)">{{ $apprenticeCount }}</div>
                    <div style="font-size: 9px; color: var(--grey)">Opportunities</div>
                </div>
            </div>

        </div>
    </div>

</div>

@endsection