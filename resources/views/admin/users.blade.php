@extends('layouts.admin')

@section('page_title', 'Users')

@section('content')

<div class="ph">
    <div class="bc">
        Home
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Users</span>
    </div>
    <div class="ph-row">
        <div>
            <div class="pt">User Management</div>
            <div class="ps">{{ $totalCount }} registered users — Contractors, Sub-contractors, Labour, Apprentices</div>
        </div>
    </div>
</div>

<div class="card">
    {{-- Type tabs --}}
    <div class="ch">
        <div class="tabs">
            <a href="{{ route('admin.users') }}"
               class="tab {{ !request('type') ? 'act' : '' }}">All ({{ $counts['all'] }})</a>
            <a href="{{ route('admin.users', ['type' => 'contractor']) }}"
               class="tab {{ request('type') === 'contractor' ? 'act' : '' }}">Contractors ({{ $counts['contractor'] }})</a>
            <a href="{{ route('admin.users', ['type' => 'subcontractor']) }}"
               class="tab {{ request('type') === 'subcontractor' ? 'act' : '' }}">Sub-contractors ({{ $counts['subcontractor'] }})</a>
            <a href="{{ route('admin.users', ['type' => 'labour']) }}"
               class="tab {{ request('type') === 'labour' ? 'act' : '' }}">Labour ({{ $counts['labour'] }})</a>
            <a href="{{ route('admin.users', ['type' => 'apprentice']) }}"
               class="tab {{ request('type') === 'apprentice' ? 'act' : '' }}">Apprentices ({{ $counts['apprentice'] }})</a>
        </div>
    </div>

    <div class="cb" style="padding-bottom:0">
        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.users') }}" class="tbar">
            @if(request('type'))
                <input type="hidden" name="type" value="{{ request('type') }}">
            @endif

            <div class="srch">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" placeholder="Search name, email, city..."
                       value="{{ request('search') }}"/>
            </div>

            <select class="fsel" name="status" onchange="this.form.submit()">
                <option value="all"       {{ request('status','all') === 'all'       ? 'selected' : '' }}>All Status</option>
                <option value="active"    {{ request('status') === 'active'           ? 'selected' : '' }}>Active</option>
                <option value="inactive"  {{ request('status') === 'inactive'         ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ request('status') === 'suspended'        ? 'selected' : '' }}>Suspended</option>
            </select>

            <select class="fsel" name="plan" onchange="this.form.submit()">
                <option value="all"  {{ request('plan','all') === 'all'  ? 'selected' : '' }}>All Plans</option>
                <option value="free" {{ request('plan') === 'free'        ? 'selected' : '' }}>Free</option>
                <option value="paid" {{ request('plan') === 'paid'        ? 'selected' : '' }}>Subscribed</option>
            </select>

            <select class="fsel" name="insured" onchange="this.form.submit()">
                <option value="all" {{ request('insured','all') === 'all' ? 'selected' : '' }}>All</option>
                <option value="yes" {{ request('insured') === 'yes'        ? 'selected' : '' }}>Insured</option>
                <option value="no"  {{ request('insured') === 'no'         ? 'selected' : '' }}>Not Insured</option>
            </select>

            <a href="{{ route('admin.users.export', request()->all()) }}"
               class="btn btn-ol btn-sm" style="margin-left:auto">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export CSV
            </a>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"/></th>
                        <th>User</th>
                        <th>Type</th>
                        <th>City / State</th>
                        {{-- <th>Trade</th> --}}
                        <th>Experience</th>
                        <th>Insured</th>
                        {{-- <th>Background</th> --}}
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            // Récupération de l'enum (sécurité si la valeur en BDD est un int ou déjà l'objet Enum)
                            $typeEnum = $user->user_type instanceof \App\Enums\UserType 
                                ? $user->user_type 
                                : \App\Enums\UserType::tryFrom($user->user_type);

                            // Assignation des variables grâce à l'Enum
                            $typeBadge = $typeEnum ? $typeEnum->badgeClass() : 'bgr';
                            $typeLabel = $typeEnum ? $typeEnum->label() : 'Unknown';
                            $typeColor = $typeEnum ? $typeEnum->color() : '#64748B';

                            // Vos autres statuts textuels (Status et Background Check) restent identiques
                            $statusBadge = [1 => 'bg', 0 => 'bgr', 'suspended' => 'br'][$user->status] ?? 'bgr';
                            $bgBadge     = ['Cleared' => 'bg', 'Pending' => 'ba', 'Flagged' => 'br'][$user->background_check] ?? 'bgr';
                        @endphp
                        <tr>
                            <td><input type="checkbox" value="{{ $user->id }}"></td>
                           <td>
    <div class="uc">
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

        <div>
            <div class="un">{{ $user->name }} {{ $user->last_name }}</div>
            <div class="ue">{{ $user->email }}</div>
        </div>
    </div>
</td>
                            <td><span class="bdg {{ $typeBadge }}">{{ $typeLabel }}</span></td>
                            <td style="font-size:12.5px;color:var(--grey)">{{ $user->city }}, {{ $user->state }}</td>
                            {{-- <td style="font-size:12px;max-width:120px">{{ $user->trade }}</td> --}}
                            {{-- <td style="font-size:12.5px;color:var(--grey)">{{ $user->years_experience }} yrs</td> --}}
                           <td style="font-size:12.5px;color:var(--grey)">
    {{ $user->experience_display }}
</td>
                            {{-- <td style="text-align:center">
                                <span class="bdg {{ $user->is_insured ? 'bg' : 'br' }}">
                                    {{ $user->is_insured ? 'Yes' : 'No' }}
                                </span>
                            </td> --}}
                            <td style="font-size:12.5px;">
    @if($user->insurance_status['status'] === 'Document')
        {{-- <a href="{{ $user->insurance_status['url'] }}" target="_blank" style="color: #1B3D6F; font-weight: 600; text-decoration: underline;">
            📄 View File
        </a> --}}
        <span style="color: #27AE60; font-weight: 600;">Yes</span>
    @elseif($user->insurance_status['status'] === 'Yes')
        <span style="color: #27AE60; font-weight: 600;">Yes</span>
    @elseif($user->insurance_status['status'] === 'No')
        <span style="color: #E74C3C; font-weight: 600;">No</span>
    @else
        <span style="color: #cbd5e1;">—</span>
    @endif
</td>
                            {{-- <td style="text-align:center">
                                <span class="bdg {{ $bgBadge }}">{{ $user->background_check }}</span>
                            </td> --}}
                            <td style="font-size:12px">
                                {{ $user->subscriptionPlan()?->name ?? $user->activeSubscription?->plan->name ?? 'Free' }}
                            </td>
                            <td><span class="bdg {{ $statusBadge }}">{{ $user->status ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <a href="{{ route('admin.users.show', $user->id) }}"
                                       class="btn btn-ol btn-xs">View</a>
                                    <form method="POST"
                                          action="{{ route('admin.users.destroy', $user->id) }}"
                                          onsubmit="return confirm('Delete {{ $user->name }}? Cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-dn btn-xs">Del</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div style="padding:40px;text-align:center;color:var(--grey)">
                                    No users found matching your filters.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="pag">
        <div class="pi">
            Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
        </div>
        <div class="pbs">
            @if($users->onFirstPage())
                <button class="pb" disabled>‹</button>
            @else
                <a href="{{ $users->previousPageUrl() }}" class="pb">‹</a>
            @endif

            @foreach($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="pb {{ $users->currentPage() === $page ? 'act' : '' }}">
                    {{ $page }}
                </a>
            @endforeach

            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="pb">›</a>
            @else
                <button class="pb" disabled>›</button>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Select all checkbox
    document.getElementById('selectAll')?.addEventListener('change', function () {
        document.querySelectorAll('tbody input[type=checkbox]')
                .forEach(cb => cb.checked = this.checked);
    });
</script>
@endpush
