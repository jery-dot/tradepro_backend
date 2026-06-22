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
            <a href="{{ route('admin.apprenticeships', ['tab' => 'applicants']) }}"
               class="tab {{ request('tab') === 'applicants' ? 'act' : '' }}">
                Applicants ({{ $applicantCount }})
            </a>
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

            <select class="fsel" name="trade" onchange="this.form.submit()">
                <option value="all">All Trade Types</option>
                @foreach(['Carpentry','Electrical','Plumbing','Roofing','Concrete','Framing','HVAC'] as $trade)
                    <option value="{{ strtolower($trade) }}"
                            {{ request('trade') === strtolower($trade) ? 'selected' : '' }}>
                        {{ $trade }}
                    </option>
                @endforeach
            </select>

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
                            <th>Trade Type</th>
                            <th>Compensation</th>
                            <th>Location</th>
                            <th>Start Date</th>
                            <th>Duration</th>
                            <th>Questions</th>
                            <th>Applicants</th>
                            <th>Platform Pricing</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opportunities as $opp)
                            @php
                                $statusBadge = $opp->status === 'open' ? 'bg' : 'br';
                            @endphp
                            <tr>
                                <td><input type="checkbox" value="{{ $opp->id }}"/></td>
                                <td>
                                    <div class="uc">
                                        <div class="ua" style="background:var(--purple)">
                                            {{ strtoupper(substr($opp->company_name,0,2)) }}
                                        </div>
                                        <div class="un">{{ $opp->company_name }}</div>
                                    </div>
                                </td>
                                <td style="font-size:12px">{{ $opp->trade_type }}</td>
                                <td style="font-size:13px;font-weight:700;color:var(--navy)">${{ $opp->compensation }}/hr</td>
                                <td style="font-size:12.5px;color:var(--grey)">{{ $opp->location }}</td>
                                <td style="font-size:12.5px;color:var(--grey)">{{ \Carbon\Carbon::parse($opp->start_date)->format('M Y') }}</td>
                                <td style="font-size:12.5px;color:var(--grey)">{{ $opp->duration }} {{ $opp->duration_unit }}</td>
                                <td style="text-align:center"><span class="bdg bn">{{ $opp->questions_count }} questions</span></td>
                                <td style="text-align:center"><span class="bdg bp">{{ $opp->applicants_count }} applied</span></td>
                                <td style="font-size:11.5px">{{ $opp->pricing_tier }}</td>
                                <td><span class="bdg {{ $statusBadge }}">{{ $opp->status }}</span></td>
                                <td>
                                    <div style="display:flex;gap:4px">
                                        <a href="{{ route('admin.apprenticeships.show', $opp->id) }}"
                                           class="btn btn-ol btn-xs">View</a>
                                        <form method="POST"
                                              action="{{ route('admin.apprenticeships.destroy', $opp->id) }}"
                                              onsubmit="return confirm('Delete this opportunity?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-dn btn-xs">Del</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12">
                                <div style="padding:40px;text-align:center;color:var(--grey)">No opportunities found.</div>
                            </td></tr>
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
                                            {{ strtoupper(substr($applicant->user->first_name,0,1).substr($applicant->user->last_name,0,1)) }}
                                        </div>
                                        <div>
                                            <div class="un">{{ $applicant->user->first_name }} {{ $applicant->user->last_name }}</div>
                                            <div class="ue">{{ $applicant->user->city }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:12.5px">{{ $applicant->trade_interest }}</td>
                                <td style="font-size:12.5px;color:var(--grey)">{{ $applicant->user->age ?? '—' }}</td>
                                <td style="font-size:12.5px;color:var(--grey)">{{ $applicant->user->city }}, {{ $applicant->user->state }}</td>
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
                                <td><span class="bdg {{ $statusBadge }}">{{ $applicant->status }}</span></td>
                                <td>
                                    <div style="display:flex;gap:4px">
                                        <a href="{{ route('admin.apprenticeships.applicant', $applicant->id) }}"
                                           class="btn btn-ol btn-xs">View</a>
                                        <a href="mailto:{{ $applicant->user->email }}"
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
