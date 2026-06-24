@extends('layouts.admin')

@section('page_title', $user->name . ' ' . $user->last_name)

@section('content')

@php
    // Leverage the UserType Enum dynamically 
    $userTypeEnum = $user->user_type;
    $typeColor = $userTypeEnum ? $userTypeEnum->color() : '#64748B';
    $typeLabel = $userTypeEnum ? $userTypeEnum->label() : 'Unknown';
    $typeBadge = $userTypeEnum ? $userTypeEnum->badgeClass() : 'bgr';
    
    // Status badges alignment mapping
    $statusBadge = [$user->status == 1 ? 'active' : 'inactive' => 'bg']['active'] ?? 'bgr';
    if ($user->status == 0) { $statusBadge = 'bgr'; }
    
    // Resolve insurance data matrix array
    $insuranceData = $user->insurance_status;
@endphp

<div class="ph">
    <div class="bc">
        Home
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <a href="{{ route('admin.users') }}" style="color:var(--grey)">Users</a>
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>{{ $user->name }} {{ $user->last_name }}</span>
    </div>
    <div class="ph-row">
        <div>
            <div class="pt">User Profile</div>
            <div class="ps">Full profile details and admin actions</div>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn-ol btn-sm">← Back to Users</a>
    </div>
</div>

<div class="g2" style="align-items:start">

    {{-- Left: Profile card --}}
    <div class="card">
        <div class="ch"><div class="ct">Profile</div></div>
        <div class="cb">
            <div class="dpav-w" style="text-align: center; margin-bottom: 20px;">
                @php
                    $imagePath = 'profiles/' . $user->profile_image;
                    $hasValidImage = !empty($user->profile_image) && file_exists(public_path($imagePath));
                @endphp

                @if($hasValidImage)
                    <img src="{{ asset($imagePath) }}" 
                        alt="{{ $user->name }}" 
                        style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid {{ $typeColor }}; margin: 0 auto 10px auto; display: block;">
                @else
                    <div style="background:{{ $typeColor }}; display: flex; align-items: center; justify-content: center; width: 90px; height: 90px; border-radius: 50%; color: #fff; font-weight: bold; font-size: 28px; margin: 0 auto 10px auto;">
                        {{ strtoupper(substr($user->name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    </div>
                @endif
                
                <div class="dpn" style="font-size:18px; font-weight:700; margin-top:5px;">{{ $user->name }} {{ $user->last_name }}</div>
                <div class="dpr" style="color:var(--grey); margin-top:3px;">{{ $typeLabel }} — {{ $user->location_text ?? 'No Location Added' }}</div>
                
                <div style="display:flex; gap:6px; margin-top:12px; flex-wrap:wrap; justify-content:center">
                    <span class="bdg {{ $user->status == 1 ? 'bg' : 'bgr' }}">{{ $user->status == 1 ? 'Active' : 'Inactive' }}</span>
                    <span class="bdg {{ $typeBadge }}">{{ $typeLabel }}</span>
                    @if($insuranceData['status'] === 'Document' || $insuranceData['status'] === 'Yes')
                        <span class="bdg bg">Insured</span>
                    @else
                        <span class="bdg br">Not Insured</span>
                    @endif
                </div>
            </div>

            <div class="dp-sec">Account Info</div>
            <div class="dp-row"><span class="dpk">Email</span><span class="dpv">{{ $user->email }}</span></div>
            <div class="dp-row"><span class="dpk">Location</span><span class="dpv">{{ $user->location_text ?: '—' }}</span></div>
            <div class="dp-row"><span class="dpk">Joined</span><span class="dpv">{{ $user->created_at->format('d M Y') }}</span></div>
            <div class="dp-row">
                <span class="dpk">Plan</span>
                <span class="dpv">
                    {{ $user->subscriptionPlan()?->name ?? $user->activeSubscription?->plan_name ?? 'Free' }}
                </span>
            </div>

            <div class="dp-sec">Trade Info</div>
            <div class="dp-row"><span class="dpk">Experience Level</span><span class="dpv">{{ $user->experience_display }}</span></div>
            <div class="dp-row"><span class="dpk">Available Today</span><span class="dpv">{{ $user->available_today ? 'Yes' : 'No' }}</span></div>
            <div class="dp-row">
                <span class="dpk">Insurance Proof</span>
                <span class="dpv">
                    @if($insuranceData['url'])
                        <a href="{{ asset($insuranceData['url']) }}" target="_blank" class="bdg bn" style="text-decoration:none">View Document ↗</a>
                    @else
                        <span class="bdg bgr">{{ $insuranceData['status'] }}</span>
                    @endif
                </span>
            </div>

            <div class="dp-sec">Ratings &amp; Actions Metric</div>
            <div class="dp-row">
                <span class="dpk">Rating Rating</span>
                <span class="dpv" style="font-weight:bold; color:var(--navy)">
                    ★ {{ $user->ratings_data['rating'] }} ({{ $user->ratings_data['ratings_count'] }} reviews)
                </span>
            </div>
        </div>
    </div>

    {{-- Right: Admin actions + activity --}}
    <div style="display:flex; flex-direction:column; gap:16px; flex:1">

        {{-- Admin Actions --}}
        <div class="card">
            <div class="ch"><div class="ct">Admin Actions</div></div>
            <div class="cb" style="display:flex; flex-direction:column; gap:10px">

                @if($user->status == 1)
                    <form method="POST" action="{{ route('admin.users.deactivate', $user->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-am" style="width:100%"
                                onclick="return confirm('Deactivate {{ $user->name }} account?')">
                            Set Inactive
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.reactivate', $user->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-pr" style="width:100%">
                            Reactivate Account
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                      onsubmit="return confirm('Permanently delete {{ $user->name }} {{ $user->last_name }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-dn" style="width:100%">
                        Delete Account
                    </button>
                </form>
            </div>
        </div>

        {{-- Recent posted jobs relation --}}
        <div class="card">
            <div class="ch"><div class="ct">Recent Job Posts</div></div>
            <div class="cb" style="padding:0 18px">
                @forelse($user->jobPosts ?? [] as $job)
                    <div class="ai" style="display:flex; justify-content:space-between; align-items:center; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                        <div style="flex:1">
                            <div class="at"><strong>{{ $job->title }}</strong></div>
                            <div class="ats" style="font-size:11px; color:var(--grey)">{{ $job->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="bdg {{ $job->status === 'open' ? 'bg' : 'bgr' }}">
                            {{ ucfirst($job->status ?? 'Active') }}
                        </span>
                    </div>
                @empty
                    <div style="padding:20px; text-align:center; color:var(--grey); font-size:13px">
                        No jobs posted.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection