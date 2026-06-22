@extends('layouts.admin')

@section('page_title', $user->name . ' ' . $user->last_name)

@section('content')

@php
    $typeColor = ['contractor'=>'#1B3D6F','subcontractor'=>'#F5874F','labour'=>'#27AE60','apprentice'=>'#8E44AD'][$user->type] ?? '#64748B';
    $typeLabel = ['contractor'=>'Contractor','subcontractor'=>'Sub-contractor','labour'=>'Labour','apprentice'=>'Apprentice'][$user->type] ?? $user->type;
    $typeBadge = ['contractor'=>'bn','subcontractor'=>'bo','labour'=>'bg','apprentice'=>'bp'][$user->type] ?? 'bgr';
    $statusBadge = ['active'=>'bg','inactive'=>'bgr','suspended'=>'br'][$user->status] ?? 'bgr';
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
            <div class="dpav-w">
                @php
                    // 1. Build the path to check if the file exists locally
                    $imagePath = 'profiles/' . $user->profile_image;
                    $hasValidImage = !empty($user->profile_image) && file_exists(public_path($imagePath));
                    
                    // 2. Safely get the type color from your Enum if available
                    $typeColor = $user->type?->color() ?? '#7f8c8d'; 
                @endphp

                @if($hasValidImage)
                    <img src="{{ asset($imagePath) }}" 
                        alt="{{ $user->name }}" 
                        class="ua" 
                        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid {{ $typeColor }}">
                @else
                    <div class="ua" style="background:{{ $typeColor }}; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; color: #fff; font-weight: bold;">
                        {{ strtoupper(substr($user->name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    </div>
                @endif
                {{-- <div class="dpav" style="background:{{ $typeColor }}">
                    {{ strtoupper(substr($user->first_name,0,1).substr($user->last_name,0,1)) }}
                </div> --}}
                <div class="dpn">{{ $user->first_name }} {{ $user->last_name }}</div>
                <div class="dpr">{{ $typeLabel }} — {{ $user->city }}, {{ $user->state }}</div>
                <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;justify-content:center">
                    <span class="bdg {{ $statusBadge }}">{{ $user->status }}</span>
                    <span class="bdg {{ $typeBadge }}">{{ $typeLabel }}</span>
                    @if($user->is_insured)
                        <span class="bdg bg">Insured</span>
                    @else
                        <span class="bdg br">Not Insured</span>
                    @endif
                </div>
            </div>

            <div class="dp-sec">Account Info</div>
            <div class="dp-row"><span class="dpk">Email</span><span class="dpv">{{ $user->email }}</span></div>
            {{-- <div class="dp-row"><span class="dpk">Phone</span><span class="dpv">{{ $user->phone ?? '—' }}</span></div> --}}
            <div class="dp-row"><span class="dpk">Location</span><span class="dpv">{{ $user->location_text }}</span></div>
            <div class="dp-row"><span class="dpk">Gender</span><span class="dpv">{{ $user->gender ?? '—' }}</span></div>
            <div class="dp-row"><span class="dpk">Joined</span><span class="dpv">{{ $user->created_at->format('d M Y') }}</span></div>
            <div class="dp-row"><span class="dpk">Plan</span><span class="dpv">{{ $user->subscription_plan ?? 'Free' }}</span></div>

            <div class="dp-sec">Trade Info</div>
            <div class="dp-row"><span class="dpk">Trade</span><span class="dpv">{{ $user->trade }}</span></div>
            <div class="dp-row"><span class="dpk">Experience</span><span class="dpv">{{ $user->years_experience }} years</span></div>
            <div class="dp-row"><span class="dpk">Available Now</span><span class="dpv">{{ $user->available_today ? 'Yes' : 'No' }}</span></div>
            <div class="dp-row"><span class="dpk">Seeking Apprenticeship</span><span class="dpv">{{ $user->seeking_apprenticeship ? 'Yes' : 'No' }}</span></div>

            <div class="dp-sec">Background &amp; Social</div>
            <div class="dp-row">
                <span class="dpk">Background Check</span>
                <span class="dpv">
                    @php $bgBadge = ['Cleared'=>'bg','Pending'=>'ba','Flagged'=>'br'][$user->background_check] ?? 'bgr' @endphp
                    <span class="bdg {{ $bgBadge }}">{{ $user->background_check }}</span>
                </span>
            </div>
            <div class="dp-row"><span class="dpk">Followers</span><span class="dpv">{{ $user->followers_count ?? 0 }}</span></div>
            <div class="dp-row"><span class="dpk">Following</span><span class="dpv">{{ $user->following_count ?? 0 }}</span></div>
            <div class="dp-row"><span class="dpk">No. of Ratings</span><span class="dpv">{{ $user->ratings_count ?? 0 }}</span></div>
        </div>
    </div>

    {{-- Right: Admin actions + activity --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Admin Actions --}}
        <div class="card">
            <div class="ch"><div class="ct">Admin Actions</div></div>
            <div class="cb" style="display:flex;flex-direction:column;gap:10px">

                @if($user->status === 1)
                    {{-- <form method="POST" action="{{ route('admin.users.suspend', $user->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-am" style="width:100%"
                                onclick="return confirm('Suspend {{ $user->first_name }}?')">
                            Suspend Account
                        </button>
                    </form>  --}}
                    <form method="POST" action="{{ route('admin.users.deactivate', $user->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-am" style="width:100%"
                                onclick="return confirm('Suspend {{ $user->first_name }}?')">
                            Set Inactive
                        </button>
                    </form>
                    {{-- <form method="POST" action="{{ route('admin.users.deactivate', $user->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-ol" style="width:100%">
                            Set Inactive
                        </button>
                    </form> --}}
                @else
                    <form method="POST" action="{{ route('admin.users.reactivate', $user->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-pr" style="width:100%">
                            Reactivate Account
                        </button>
                    </form>
                @endif

                {{-- <a href="mailto:{{ $user->email }}" class="btn btn-ol" style="width:100%;justify-content:center">
                    Send Email
                </a> --}}

                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                      onsubmit="return confirm('Permanently delete {{ $user->first_name }} {{ $user->last_name }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-dn" style="width:100%">
                        Delete Account
                    </button>
                </form>
            </div>
        </div>

        {{-- Recent jobs / activity --}}
        <div class="card">
            <div class="ch"><div class="ct">Recent Jobs</div></div>
            <div class="cb" style="padding:0 18px">
                @forelse($user->jobs ?? [] as $job)
                    <div class="ai">
                        <div class="ad" style="background:var(--orange)"></div>
                        <div style="flex:1">
                            <div class="at"><strong>{{ $job->title }}</strong> — {{ $job->company }}</div>
                            <div class="ats">{{ $job->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="bdg {{ ['open'=>'bg','closed'=>'bgr','pending'=>'ba'][$job->status] ?? 'bgr' }}">
                            {{ $job->status }}
                        </span>
                    </div>
                @empty
                    <div style="padding:20px;text-align:center;color:var(--grey);font-size:13px">
                        No jobs posted.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection
