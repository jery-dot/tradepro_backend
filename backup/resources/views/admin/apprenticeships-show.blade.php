@extends('layouts.admin')
@section('page_title', $opportunity->company_name)
@section('content')

<div class="ph">
    <div class="bc">
        Home <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <a href="{{ route('admin.apprenticeships') }}" style="color:var(--grey)">Apprenticeships</a>
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>{{ $opportunity->company_name }}</span>
    </div>
    <div class="ph-row">
        <div>
            <div class="pt">{{ $opportunity->company_name }}</div>
            <div class="ps">Apprenticeship opportunity — {{ $opportunity->trade_type }}</div>
        </div>
        <a href="{{ route('admin.apprenticeships') }}" class="btn btn-ol btn-sm">← Back</a>
    </div>
</div>

<div class="g2" style="align-items:start">

    <div class="card">
        <div class="ch"><div class="ct">Opportunity Details</div></div>
        <div class="cb">
            <div class="dpav-w">
                <div class="dpav" style="background:var(--purple);border-radius:var(--r);font-size:28px">&#127891;</div>
                <div class="dpn">{{ $opportunity->company_name }}</div>
                <div class="dpr">{{ $opportunity->trade_type }}</div>
                <span class="bdg {{ $opportunity->status === 'open' ? 'bg' : 'br' }}" style="margin-top:8px">{{ $opportunity->status }}</span>
            </div>

            <div class="dp-sec">Opportunity Info</div>
            <div class="dp-row"><span class="dpk">Compensation</span><span class="dpv" style="color:var(--navy);font-weight:700">${{ $opportunity->compensation }}/hr</span></div>
            <div class="dp-row"><span class="dpk">Location</span><span class="dpv">{{ $opportunity->location }}</span></div>
            <div class="dp-row"><span class="dpk">Start Date</span><span class="dpv">{{ \Carbon\Carbon::parse($opportunity->start_date)->format('M Y') }}</span></div>
            <div class="dp-row"><span class="dpk">Duration</span><span class="dpv">{{ $opportunity->duration }} {{ $opportunity->duration_unit }}</span></div>
            <div class="dp-row"><span class="dpk">Screening Questions</span><span class="dpv">{{ $opportunity->questions_count }}</span></div>
            <div class="dp-row"><span class="dpk">Platform Pricing</span><span class="dpv">{{ $opportunity->pricing_tier }}</span></div>
            <div class="dp-row"><span class="dpk">Total Applicants</span><span class="dpv">{{ $opportunity->applicants_count }}</span></div>

            <div class="dp-sec">About the Company</div>
            <div style="font-size:13px;color:var(--grey);line-height:1.7;padding:8px 0">{{ $opportunity->about_company }}</div>

            <div class="dp-sec">Requirements</div>
            <div style="font-size:13px;color:var(--grey);line-height:1.7;padding:8px 0">{{ $opportunity->requirements }}</div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">

        <div class="card">
            <div class="ch"><div class="ct">Admin Actions</div></div>
            <div class="cb" style="display:flex;flex-direction:column;gap:10px">
                @if($opportunity->status === 'open')
                    <form method="POST" action="{{ route('admin.apprenticeships.close', $opportunity->id) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-am" style="width:100%">Close Opportunity</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.apprenticeships.reopen', $opportunity->id) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-pr" style="width:100%">Reopen Opportunity</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.apprenticeships.destroy', $opportunity->id) }}"
                      onsubmit="return confirm('Delete this opportunity?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-dn" style="width:100%">Delete Opportunity</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="ch">
                <div class="ct">Applicants ({{ $opportunity->applicants_count }})</div>
                <a href="{{ route('admin.apprenticeships', ['tab'=>'applicants','opportunity'=>$opportunity->id]) }}"
                   class="btn btn-ol btn-sm">View All</a>
            </div>
            <div class="cb" style="padding:0 18px">
                @forelse($opportunity->applicants ?? [] as $app)
                    @php
                        $sb = ['pending'=>'ba','accepted'=>'bg','rejected'=>'br'][$app->status] ?? 'bgr';
                    @endphp
                    <div class="ai">
                        <div class="ad" style="background:var(--purple)"></div>
                        <div style="flex:1">
                            <div class="at"><strong>{{ $app->user->first_name }} {{ $app->user->last_name }}</strong></div>
                            <div class="ats">{{ $app->trade_interest }} &mdash; {{ $app->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="bdg {{ $sb }}">{{ $app->status }}</span>
                    </div>
                @empty
                    <div style="padding:20px;text-align:center;color:var(--grey);font-size:13px">No applicants yet.</div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection
