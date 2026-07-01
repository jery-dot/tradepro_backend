@extends('layouts.admin')

@section('page_title', 'Subscriptions')

@section('content')

<div class="ph">
    <div class="bc">
        Home
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Subscriptions</span>
    </div>
    <div class="ph-row">
        <div>
            <div class="pt">Subscription Plans</div>
            <div class="ps">Manage platform plans and monitor active subscribers</div>
        </div>
    </div>
</div>

{{-- Plan Cards --}}
<div class="g3" style="margin-bottom:20px">

    {{-- Labourer Plan --}}
    <div class="card plan-c">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <div class="plan-n">Labourer</div>
            <span class="bdg bn">{{ $plans['labourer']['count'] }} active</span>
        </div>
        <div class="plan-p">$19.99<span>/month</span></div>
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:5px;font-size:12px;color:var(--grey)">
            <div>✓ Unlimited job applications</div>
            <div>✓ Direct messaging with employers</div>
            <div>✓ Profile visibility</div>
            <div>✓ Job alerts</div>
            <div>✓ Rating system</div>
        </div>
        <div style="margin-top:14px;font-size:13px;font-weight:700;color:var(--navy)">
            MRR: ${{ number_format($plans['labourer']['mrr']) }}
        </div>
    </div>

    {{-- Contractor Plan (featured) --}}
    <div class="card plan-c feat">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <div class="plan-n">Contractor / Sub-contractor</div>
            <span class="bdg bo">{{ $plans['contractor']['count'] }} active</span>
        </div>
        <div class="plan-p">$59.99<span>/month</span></div>
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:5px;font-size:12px;color:var(--grey)">
            <div>✓ Post unlimited jobs</div>
            <div>✓ Access to all applicants</div>
            <div>✓ Featured listings</div>
            <div>✓ Direct hire tools</div>
            <div>✓ Analytics dashboard</div>
        </div>
        <div style="margin-top:14px;font-size:13px;font-weight:700;color:var(--orange)">
            MRR: ${{ number_format($plans['contractor']['mrr']) }}
        </div>
    </div>

    {{-- Apprentice Plan --}}
    <div class="card plan-c">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <div class="plan-n">Apprentice</div>
            <span class="bdg bp">{{ $plans['apprentice']['count'] }} active</span>
        </div>
        <div class="plan-p">$9.99<span>/month</span></div>
        <div style="margin-top:5px;font-size:11px;color:var(--teal);font-weight:600">Free for 3 months</div>
        <div style="margin-top:10px;display:flex;flex-direction:column;gap:5px;font-size:12px;color:var(--grey)">
            <div>✓ Access apprenticeship hub</div>
            <div>✓ Connect with contractors</div>
            <div>✓ Profile &amp; portfolio</div>
            <div>✓ Job alerts</div>
        </div>
        <div style="margin-top:14px;font-size:13px;font-weight:700;color:var(--purple)">
            MRR: ${{ number_format($plans['apprentice']['mrr']) }}
        </div>
    </div>
</div>

{{-- Subscribers table --}}
<div class="card">
    <div class="ch">
        <div class="ct">Active Subscribers</div>
        <form method="GET" action="{{ route('admin.subscriptions') }}">
            <select class="fsel" name="plan" onchange="this.form.submit()">
                <option value="all">All Plans</option>
                <option value="labourer"    {{ request('plan') === 'labourer'    ? 'selected':'' }}>Labourer</option>
                <option value="contractor"  {{ request('plan') === 'contractor'  ? 'selected':'' }}>Contractor</option>
                <option value="apprentice"  {{ request('plan') === 'apprentice'  ? 'selected':'' }}>Apprentice</option>
            </select>
        </form>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Type</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    {{-- <th>Billing</th>
                    <th>Started</th>
                    <th>Next Billing</th> --}}
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    @forelse($subscriptions as $sub)
        @php
            // Safe fallback properties if user is deleted or missing
            $userTypeEnum = $sub->user?->user_type; // This will automatically be an instance of UserType Enum
            $firstName = $sub->user?->name ?? 'User';
            $lastName = $sub->user ? "" : 'Deleted';
            
            // Resolve badges and labels cleanly using your Enum methods
            $typeBadge = $userTypeEnum ? $userTypeEnum->badgeClass() : 'bgr';
            $typeLabel = $userTypeEnum ? $userTypeEnum->label() : 'Unknown';
            // dd($userTypeEnum);
            
            $planBadge = ['Contractor' => 'bo', 'Labourer' => 'bn', 'Apprentice' => 'bp'][$sub->plan_name] ?? 'bgr';
            
            // Clean extraction of string value from the custom status Attribute
            $statusVal = is_string($sub->status) ? $sub->status : (string)($sub->status?->value ?? $sub->status);
            $statusBadge = ['active' => 'bg', 'cancelled' => 'br', 'trial' => 'ba', 'expired' => 'bgr'][$statusVal] ?? 'bgr';
        @endphp
        <tr>
            <td style="font-size:13px;font-weight:600">
                {{ $firstName }} {{ $lastName }}
            </td>
            <td><span class="bdg {{ $typeBadge }}">{{ $typeLabel }}</span></td>
            <td><span class="bdg {{ $planBadge }}">{{ $sub->plan->name }}</span></td>
            <td style="font-size:13px;font-weight:700;color:var(--navy)">
                {{ $sub->plan->price > 0 ? '$'.number_format($sub->plan->price, 2) : 'Free' }}
            </td>
            {{-- <td style="font-size:12.5px;color:var(--grey)">{{ ucfirst($sub->billing_cycle) }}</td>
            <td style="font-size:12.5px;color:var(--grey)">
                {{ $sub->started_at ? \Carbon\Carbon::parse($sub->started_at)->format('d M Y') : '—' }}
            </td>
            <td style="font-size:12.5px;color:var(--grey)">
                {{ $sub->next_billing_at ? \Carbon\Carbon::parse($sub->next_billing_at)->format('d M Y') : '—' }}
            </td> --}}
            <td><span class="bdg {{ $statusBadge }}">{{ ucfirst($statusVal) }}</span></td>
            <td>
                <div style="display:flex;gap:4px">
                    <a href="{{ route('admin.subscriptions.show', $sub->id) }}"
                       class="btn btn-ol btn-xs">View</a>
                    @if($statusVal === 'active')
                        <form method="POST"
                              action="{{ route('admin.subscriptions.cancel', $sub->id) }}"
                              onsubmit="return confirm('Cancel subscription for {{ $firstName }}?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-dn btn-xs">Cancel</button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9">
                <div style="padding:40px;text-align:center;color:var(--grey)">No subscriptions found.</div>
            </td>
        </tr>
    @endforelse
</tbody>
        </table>
    </div>

    <div class="pag">
        <div class="pi">
            Showing {{ $subscriptions->firstItem() }}–{{ $subscriptions->lastItem() }} of {{ $subscriptions->total() }}
        </div>
        <div class="pbs">
            @if($subscriptions->onFirstPage())
                <button class="pb" disabled>‹</button>
            @else
                <a href="{{ $subscriptions->previousPageUrl() }}" class="pb">‹</a>
            @endif
            @foreach($subscriptions->getUrlRange(1, min($subscriptions->lastPage(), 7)) as $page => $url)
                <a href="{{ $url }}" class="pb {{ $subscriptions->currentPage() === $page ? 'act' : '' }}">{{ $page }}</a>
            @endforeach
            @if($subscriptions->hasMorePages())
                <a href="{{ $subscriptions->nextPageUrl() }}" class="pb">›</a>
            @else
                <button class="pb" disabled>›</button>
            @endif
        </div>
    </div>
</div>

@endsection
