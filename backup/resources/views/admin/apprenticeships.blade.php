@extends('layouts.admin')

@section('page_title', 'Apprenticeships')

@section('content')

<div class="ph">
    <div class="bc">
        Home
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Apprenticeships</span>
    </div>
    <div class="ph-row">
        <div>
            <div class="pt">Apprenticeship Hub</div>
            <div class="ps">Manage opportunities posted by contractors and applications from apprentices</div>
        </div>
    </div>
</div>

<div class="card">
    {{-- Sub-tabs: Opportunities / Applicants --}}
    <div class="ch">
        <div class="tabs">
            <a href="{{ route('admin.apprenticeships', ['tab' => 'opportunities']) }}"
               class="tab {{ request('tab', 'opportunities') === 'opportunities' ? 'act' : '' }}">
                Opportunities ({{ $opportunityCount }})
            </a>
            {{-- <a href="{{ route('admin.apprenticeships', ['tab' => 'applicants']) }}"
               class="tab {{ request('tab') === 'applicants' ? 'act' : '' }}">
                Applicants ({{ $applicantCount }})
            </a> --}}
        </div>
    </div>

    <div class="cb" style="padding-bottom:0">
        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.apprenticeships') }}" class="tbar">
            <input type="hidden" name="tab" value="{{ request('tab', 'opportunities') }}">

            <div class="srch">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" placeholder="Search company, trade type..."
                       value="{{ request('search') }}"/>
            </div>

            {{-- <select class="fsel" name="trade" onchange="this.form.submit()">
                <option value="all">All Trade Types</option>
                @foreach(['Carpentry','Electrical','Plumbing','Roofing','Concrete','Framing','HVAC'] as $trade)
                    <option value="{{ strtolower($trade) }}"
                            {{ request('trade') === strtolower($trade) ? 'selected' : '' }}>
                        {{ $trade }}
                    </option>
                @endforeach
            </select> --}}

            {{-- <select class="fsel" name="location" onchange="this.form.submit()">
                <option value="all">All Locations</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc }}" {{ request('location') === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                @endforeach
            </select> --}}
        </form>

        {{-- OPPORTUNITIES TABLE --}}
        @if(request('tab', 'opportunities') === 'opportunities')
            <div class="tw">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox"/></th>
                            <th>Company</th>
                            <th>Opportunity Title</th>
                            <th>Description</th> <th>Compensation</th>
                            <th>Location</th>
                            <th>Start Date</th>
                            <th>Duration</th>
                            <th>Questions</th>
                            <th>Applicants</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opportunities as $opp)
                            @php
                                // Fallback configuration if status isn't explicitly set
                                $status = $opp->status ?? 'open';
                                $statusBadge = $status === 'open' ? 'bg' : 'br';
                                
                                // Resolve company name via the user relationship safely
                                $companyName = $opp->user->company_name ?? $opp->user->name ?? 'Trade Pro Partner';
                            @endphp
                            <tr>
                                <td><input type="checkbox" value="{{ $opp->id }}"/></td>
                                <td>
                                    <div class="uc">
                                        <div class="ua" style="background:var(--purple)">
                                            {{ strtoupper(substr($companyName, 0, 2)) }}
                                        </div>
                                        <div class="un">{{ $companyName }}</div>
                                    </div>
                                </td>
                                <td style="font-size:12px; font-weight: 500;">{{ $opp->title }}</td>
                                <td style="max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12.5px; color: var(--grey);" title="{{ $opp->description }}">
                                    {{ Str::limit($opp->apprenticeship_description, 60, '...') }}
                                </td>
                                <td style="font-size:13px; font-weight:700; color:var(--navy)">
                                    @if($opp->compensation_paid)
                                        ${{ number_format($opp->total_pay_offering, 2) }}
                                    @else
                                        Unpaid
                                    @endif
                                </td>
                                <td style="font-size:12.5px; color:var(--grey)">{{ $opp->city }}</td>
                                <td style="font-size:12.5px; color:var(--grey)">
                                    {{ $opp->apprenticeship_start_date ? \Carbon\Carbon::parse($opp->apprenticeship_start_date)->format('M Y') : 'N/A' }}
                                </td>
                                <td style="font-size:12.5px; color:var(--grey)">{{ $opp->duration_weeks }} Weeks</td>
                                <td style="text-align:center"><span class="bdg bn">{{ $opp->questions_count ?? 0 }} questions</span></td>
                                <td style="text-align:center"><span class="bdg bp">{{ $opp->applicants_count ?? 0 }} applied</span></td>
                                <td><span class="bdg {{ $statusBadge }}">{{ ucfirst($status) }}</span></td>
                                <td>
                                    <div style="display:flex; gap:4px">
                                        <a href="{{ route('admin.apprenticeships.show', $opp->id) }}" class="btn btn-ol btn-xs">View</a>
                                        <form method="POST" action="{{ route('admin.apprenticeships.destroy', $opp->id) }}" onsubmit="return confirm('Delete this opportunity?')">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-dn btn-xs">Del</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11">
                                    <div style="padding:40px; text-align:center; color:var(--grey)">No opportunities found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        {{-- APPLICANTS TABLE --}}
        @else
            <div class="tw">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox"/></th>
                            <th>Applicant</th>
                            <th>Trade Interest</th>
                            <th>Age</th>
                            <th>Location</th>
                            <th>Education</th>
                            <th>About Me</th>
                            <th>Resume</th>
                            <th>Applied For</th>
                            <th>Profile Visible</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applicants as $applicant)
                            @php
                                $statusBadge = ['pending'=>'ba','accepted'=>'bg','rejected'=>'br'][$applicant->status] ?? 'bgr';
                            @endphp
                            <tr>
                                <td><input type="checkbox" value="{{ $applicant->id }}"/></td>
                                <td>
                                    <div class="uc">
                                        <div class="ua" style="background:var(--purple)">
                                            {{ strtoupper(substr($applicant->name,0,1).substr($applicant->last_name ?? ' ',0,1)) }}
                                        </div>
                                        <div>
                                            <div class="un">{{ $applicant->name }} {{ $applicant->last_name }}</div>
                                            <div class="ue">{{ $applicant->city }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:12.5px">{{ $applicant->trade_interest }}</td>
                                <td style="font-size:12.5px;color:var(--grey)">{{ $applicant->age ?? '—' }}</td>
                                <td style="font-size:12.5px;color:var(--grey)">{{ $applicant->city }}, {{ $applicant->state }}</td>
                                <td style="font-size:12.5px;color:var(--grey)">{{ $applicant->educational_background }}</td>
                                <td style="font-size:12px;color:var(--grey);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                    title="{{ $applicant->about_me }}">
                                    {{ $applicant->about_me }}
                                </td>
                                <td style="text-align:center">
                                    <span class="bdg {{ $applicant->resume_path ? 'bg' : 'bgr' }}">
                                        {{ $applicant->resume_path ? 'Uploaded' : 'None' }}
                                    </span>
                                </td>
                                <td style="font-size:12.5px">{{ $applicant->opportunity->company_name ?? '—' }}</td>
                                <td style="text-align:center">
                                    <span class="bdg {{ $applicant->profile_visible ? 'bg' : 'bgr' }}">
                                        {{ $applicant->profile_visible ? 'Visible' : 'Hidden' }}
                                    </span>
                                </td>
                                <td>
                                    @php        
                                        // 1. Map the numeric status to a human-readable string
                                        $statusText = $applicant->status == 1 ? 'Approved' : 'Pending';
                                        
                                        // 2. Set the badge class ('bg' for green/active, 'br' for red/gray/pending)
                                        $statusBadge = $applicant->status == 1 ? 'bg' : 'br';
                                    @endphp
                                    
                                    <span class="bdg {{ $statusBadge }}">{{ $statusText }}</span>
                                </td>
                                {{-- <td><span class="bdg {{ $statusBadge }}">{{ $applicant->status }}</span></td> --}}
                                <td>
                                    <div style="display:flex;gap:4px">
                                        {{-- <a href="{{ route('admin.apprenticeships.applicant', $applicant->id) }}"
                                           class="btn btn-ol btn-xs">View</a> --}}
                                        <a href="mailto:{{ $applicant->email }}"
                                           class="btn btn-or btn-xs">Msg</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12">
                                <div style="padding:40px;text-align:center;color:var(--grey)">No applicants found.</div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    <div class="pag">
        @php $paginator = request('tab') === 'applicants' ? $applicants : $opportunities; @endphp
        <div class="pi" id="aPagI">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </div>
        <div class="pbs">
            @if($paginator->onFirstPage())
                <button class="pb" disabled>‹</button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pb">‹</a>
            @endif
            @foreach($paginator->getUrlRange(1, min($paginator->lastPage(), 7)) as $page => $url)
                <a href="{{ $url }}" class="pb {{ $paginator->currentPage() === $page ? 'act' : '' }}">{{ $page }}</a>
            @endforeach
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pb">›</a>
            @else
                <button class="pb" disabled>›</button>
            @endif
        </div>
    </div>
</div>

@endsection
