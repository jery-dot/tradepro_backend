@extends('layouts.admin')
@section('page_title', $job->title)
@section('content')

@php
    // Updated to use your correct relationship reference ($job->specialization->name)
    $specName    = $job->specialization->name ?? '';
    $typeColor   = ['contractor'=>'#1B3D6F','subcontractor'=>'#F5874F','labour'=>'#27AE60'][$specName] ?? '#64748B';
    $typeLabel   = ['contractor'=>'Contractor','subcontractor'=>'Sub-contractor','labour'=>'Labour'][$specName] ?? $specName;
    $typeBadge   = ['contractor'=>'bn','subcontractor'=>'bo','labour'=>'bg'][$specName] ?? 'bgr';
    
    $statusBadge = ['open'=>'bg','pending'=>'ba','completed'=>'bt','closed'=>'bgr'][$job->status] ?? 'bgr';

    // Dynamic currency symbols and rate formats
    $currencySymbol = ['USD' => '$', 'EUR' => '€', 'GBP' => '£'][$job->pay_rate_currency] ?? $job->pay_rate_currency;
    $rateTypeShort  = ['hour' => 'hr', 'monthly' => 'mo', 'yearly' => 'yr'][$job->pay_rate_type] ?? $job->pay_rate_type;
@endphp

<div class="ph">
    <div class="bc">
        Home <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <a href="{{ route('admin.jobs') }}" style="color:var(--grey)">Jobs</a>
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>{{ $job->title }}</span>
    </div>
    <div class="ph-row">
        <div>
            <div class="pt">{{ $job->title }}</div>
            <div class="ps">{{ $job->company_name }} &mdash; posted {{ $job->created_at->diffForHumans() }}</div>
        </div>
        <a href="{{ route('admin.jobs') }}" class="btn btn-ol btn-sm">← Back to Jobs</a>
    </div>
</div>

<div class="g2" style="align-items:start">

    <div class="card">
        <div class="ch"><div class="ct">Job Details</div></div>
        <div class="cb">
            <div class="dpav-w">
                <div class="dpav" style="background:{{ $typeColor }};border-radius:var(--r);font-size:28px">&#128188;</div>
                <div class="dpn">{{ $job->title }}</div>
                <div class="dpr">{{ $job->company_name }} &mdash; ★{{ $job->company_rating }}</div>
                <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;justify-content:center">
                    <span class="bdg {{ $statusBadge }}">{{ $job->status }}</span>
                    <span class="bdg {{ $typeBadge }}">{{ $typeLabel }}</span>
                    @if($job->is_featured)<span class="bdg bo">Featured</span>@endif
                </div>
            </div>

            <div class="dp-sec">Job Information</div>
            <div class="dp-row"><span class="dpk">Type Required</span><span class="dpv">{{ $typeLabel }}</span></div>
            <div class="dp-row">
                <span class="dpk">Pay Rate</span>
                <span class="dpv" style="color:var(--navy);font-weight:700">
                    {{ $currencySymbol }}{{ $job->pay_rate_amount }}/{{ $rateTypeShort }}
                </span>
            </div>
            <div class="dp-row"><span class="dpk">Location</span><span class="dpv">{{ $job->formatted_location }}</span></div>
            <div class="dp-row"><span class="dpk">Start Date</span><span class="dpv">{{ \Carbon\Carbon::parse($job->start_date)->format('d M Y') }}</span></div>
            <div class="dp-row"><span class="dpk">Duration</span><span class="dpv">{{ $job->formatted_duration }}</span></div>
            <div class="dp-row"><span class="dpk">Available Today</span><span class="dpv">{{ $job->is_available ? 'Yes' : 'No' }}</span></div>
            <div class="dp-row"><span class="dpk">Featured Posting</span><span class="dpv">{{ $job->is_featured ? 'Yes — $10 fee' : 'No' }}</span></div>
            <div class="dp-row"><span class="dpk">Posted By</span><span class="dpv">{{ $job->poster->first_name ?? '' }} {{ $job->poster->last_name ?? '' }}</span></div>

            <div class="dp-sec">Skills Required</div>
            <div style="padding:8px 0;display:flex;flex-wrap:wrap;gap:4px">
                @foreach(json_decode($job->skills ?? '[]') as $skill)
                    <span class="skill-chip">{{ $skill->name }}</span>
                @endforeach
            </div>

            <div class="dp-sec">Job Description</div>
            <div style="font-size:13px;color:var(--grey);line-height:1.7;padding:8px 0">
                {{ $job->description }}
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="ch"><div class="ct">Admin Actions</div></div>
            <div class="cb" style="display:flex;flex-direction:column;gap:10px">
                @if($job->status === 'open')
                    <form method="POST" action="{{ route('admin.jobs.close', $job->id) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-am" style="width:100%">Close Listing</button>
                    </form>
                @elseif($job->status === 'closed')
                    <form method="POST" action="{{ route('admin.jobs.reopen', $job->id) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-pr" style="width:100%">Reopen Listing</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.jobs.destroy', $job->id) }}"
                      onsubmit="return confirm('Delete this job? Cannot be undone.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-dn" style="width:100%">Delete Job</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="ch"><div class="ct">Applicants</div></div>
            <div class="cb" style="padding:0 18px">
                @forelse($job->applicants ?? [] as $applicant)
                    <div class="ai">
                        <div class="ad" style="background:var(--navy)"></div>
                        <div style="flex:1">
                            <div class="at"><strong>{{ $applicant->user->first_name ?? '' }} {{ $applicant->user->last_name ?? '' }}</strong></div>
                            <div class="ats">{{ $applicant->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="bdg {{ ['pending'=>'ba','accepted'=>'bg','rejected'=>'br'][$applicant->status] ?? 'bgr' }}">{{ $applicant->status }}</span>
                    </div>
                @empty
                    <div style="padding:20px;text-align:center;color:var(--grey);font-size:13px">No applicants yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection