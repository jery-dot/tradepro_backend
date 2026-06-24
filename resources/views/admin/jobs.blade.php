@extends('layouts.admin')

@section('page_title', 'Jobs')

@section('content')

<div class="ph">
    <div class="bc">
        Home
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Jobs</span>
    </div>
    <div class="ph-row">
        <div>
            <div class="pt">Jobs Management</div>
            <div class="ps">{{ $totalCount }} job listings — view, search, filter and manage all platform jobs</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="cb" style="padding-bottom:0">
        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.jobs') }}" class="tbar">
            <div class="srch">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" placeholder="Search title, company, location..."
                       value="{{ request('search') }}"/>
            </div>

            <select class="fsel" name="status" onchange="this.form.submit()">
                <option value="all"       {{ request('status','all') === 'all'       ? 'selected':'' }}>All Status</option>
                <option value="open"      {{ request('status') === 'open'             ? 'selected':'' }}>Open</option>
                <option value="pending"   {{ request('status') === 'pending'          ? 'selected':'' }}>Pending</option>
                <option value="completed" {{ request('status') === 'completed'        ? 'selected':'' }}>Completed</option>
                <option value="closed"    {{ request('status') === 'closed'           ? 'selected':'' }}>Closed</option>
            </select>

            <select class="fsel" name="type" onchange="this.form.submit()">
                <option value="all"           {{ request('type','all') === 'all'           ? 'selected':'' }}>All Types</option>
                <option value="contractor"    {{ request('type') === 'contractor'           ? 'selected':'' }}>Contractor</option>
                <option value="subcontractor" {{ request('type') === 'subcontractor'        ? 'selected':'' }}>Sub-contractor</option>
                <option value="labour"        {{ request('type') === 'labour'               ? 'selected':'' }}>Labour</option>
            </select>

            <select class="fsel" name="available" onchange="this.form.submit()">
                <option value="all" {{ request('available','all') === 'all' ? 'selected':'' }}>All</option>
                <option value="1"   {{ request('available') === '1'          ? 'selected':'' }}>Available Today</option>
            </select>

            <a href="{{ route('admin.jobs.export', request()->all()) }}"
               class="btn btn-ol btn-sm" style="margin-left:auto">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                </svg>
                Export
            </a>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th><input type="checkbox"/></th>
                            <th>Job Code</th>
                            <th>Posted By</th> 
                            <th>Type</th>
                            <th>Skills Needed</th>
                            <th>Description</th> 
                            <th>Pay/hr</th>
                            <th>Location</th>
                            <th>Duration</th>
                            <th>Start Date</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $job) 
                            @php
                                $typeBadge = ['contractor'=>'bn','subcontractor'=>'bo','labour'=>'bg'][$job->specialization->name] ?? 'bgr';
                                $typeLabel = ['contractor'=>'Contractor','subcontractor'=>'Sub-contractor','labour'=>'Labour'][$job->specialization->name] ?? $job->specialization->name;
                                $statusBadge = ['open'=>'bg','pending'=>'ba','completed'=>'bt','closed'=>'bgr'][$job->status] ?? 'bgr';
                            @endphp
                            <tr>
                                <td><input type="checkbox" value="{{ $job->id }}"/></td>
                                <td style="font-size:12.5px;color:var(--grey)">{{ $job->job_code }}</td>
                                
                                <td>
                                    <div style="font-weight: 600; color: var(--navy)">{{ $job->owner?->name ?? 'Unknown User' }}</div>
                                    <div style="font-size: 11px; color: var(--grey)">{{ $job->owner?->email ?? '' }}</div>
                                </td>

                                <td><span class="bdg {{ $typeBadge }}">{{ $job->specialization->name }}</span></td>
                                <td style="max-width:140px">
                                    @foreach(json_decode($job->skills) as $skill)
                                        <span class="skill-chip">{{ $skill->name }}</span>
                                    @endforeach
                                </td>
                                
                                <td style="max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12.5px; color: var(--grey);" title="{{ $job->description }}">
                                    {{ Str::limit($job->description, 60, '...') }}
                                </td>
                                
                                <td style="font-size:13px;font-weight:700;color:var(--navy)">
                                    @php
                                        $currencySymbol = ['USD' => '$', 'EUR' => '€', 'GBP' => '£'][$job->pay_rate_currency] ?? $job->pay_rate_currency;
                                        $rateTypeShort  = ['hour' => 'hr', 'monthly' => 'mo', 'yearly' => 'yr'][$job->pay_rate_type] ?? $job->pay_rate_type;
                                    @endphp

                                    {{ $currencySymbol }}{{ $job->pay_rate_amount }}/{{ $rateTypeShort }}
                                </td>
                                <td style="font-size:12.5px;color:var(--grey)">{{ $job->formatted_location }}</td>
                                <td style="font-size:12.5px;color:var(--grey)">{{ $job->formatted_duration }}</td>
                                <td style="font-size:12.5px;color:var(--grey)">{{ \Carbon\Carbon::parse($job->start_date)->format('d M Y') }}</td>
                                <td style="text-align:center">
                                    <span class="bdg {{ $job->is_featured ? 'bo' : 'bgr' }}">
                                        {{ $job->is_featured ? 'Featured' : 'No' }}
                                    </span>
                                </td>
                                <td><span class="bdg {{ $statusBadge }}">{{ $job->status }}</span></td>
                                <td>
                                    <div style="display:flex;gap:4px">
                                        <a href="{{ route('admin.jobs.show', $job->id) }}" class="btn btn-ol btn-xs">View</a>
                                        <form method="POST"
                                            action="{{ route('admin.jobs.destroy', $job->id) }}"
                                            onsubmit="return confirm('Delete this job?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-dn btn-xs">Del</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13"> 
                                    <div style="padding:40px;text-align:center;color:var(--grey)">
                                        No jobs found matching your filters.
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
        <div class="pi">Showing {{ $jobs->firstItem() }}–{{ $jobs->lastItem() }} of {{ $jobs->total() }} jobs</div>
        <div class="pbs">
            @if($jobs->onFirstPage())
                <button class="pb" disabled>‹</button>
            @else
                <a href="{{ $jobs->previousPageUrl() }}" class="pb">‹</a>
            @endif
            @foreach($jobs->getUrlRange(1, min($jobs->lastPage(), 7)) as $page => $url)
                <a href="{{ $url }}" class="pb {{ $jobs->currentPage() === $page ? 'act' : '' }}">{{ $page }}</a>
            @endforeach
            @if($jobs->hasMorePages())
                <a href="{{ $jobs->nextPageUrl() }}" class="pb">›</a>
            @else
                <button class="pb" disabled>›</button>
            @endif
        </div>
    </div>
</div>

@endsection
