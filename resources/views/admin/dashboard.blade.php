@extends('layouts.admin')

@section('page_title', 'Dashboard')

@section('content')

<div class="ph">
    <div class="bc">
        Home
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Dashboard</span>
    </div>
    <div class="ph-row">
        <div>
            <div class="pt">Platform Overview</div>
            <div class="ps">Welcome back — here's what's happening on TradePro today.</div>
        </div>
        {{-- <div style="display:flex;gap:7px">
            <button class="btn btn-ol btn-sm">Last 30 days</button>
            <a href="{{ route('admin.export') }}" class="btn btn-or btn-sm">Export</a>
        </div> --}}
    </div>
</div>

{{-- Stat Cards --}}
<div class="sg">
    <div class="sc">
        <div class="sci" style="background:#e8edf8">
            <svg fill="none" viewBox="0 0 24 24" stroke="#1B3D6F" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div>
            <div class="sv">{{ $stats['total_users'] }}</div>
            <div class="slb">Total Users</div>
            <div class="sch up">↑ {{ $stats['new_users_week'] }} this week</div>
        </div>
    </div>

    <div class="sc">
        <div class="sci" style="background:var(--orange-l)">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--orange)" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2"/>
                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
            </svg>
        </div>
        <div>
            <div class="sv">{{ $stats['active_jobs'] }}</div>
            <div class="slb">Active Jobs</div>
            <div class="sch up">↑ {{ $stats['new_jobs_today'] }} today</div>
        </div>
    </div>

    <div class="sc">
        <div class="sci" style="background:var(--green-bg)">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--green)" stroke-width="2">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
        </div>
        <div>
            <div class="sv">{{ $stats['apprenticeships'] }}</div>
            <div class="slb">Apprenticeships</div>
            <div class="sch up">↑ {{ $stats['new_apprenticeships'] }} new</div>
        </div>
    </div>

    <div class="sc">
        <div class="sci" style="background:var(--purple-bg)">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--purple)" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
            </svg>
        </div>
        <div>
            <div class="sv">{{ $stats['marketplace_listings'] }}</div>
            <div class="slb">Marketplace</div>
            <div class="sch dn">↓ {{ $stats['removed_listings'] }} removed</div>
        </div>
    </div>

    <div class="sc">
        <div class="sci" style="background:var(--teal-bg)">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--teal)" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>
        <div>
            <div class="sv">${{ number_format($stats['mrr']) }}</div>
            <div class="slb">Monthly Revenue</div>
            <div class="sch up">↑ {{ $stats['mrr_growth'] }}%</div>
        </div>
    </div>
</div>

{{-- Charts row --}}
<div class="g6040" style="margin-bottom:16px">

    {{-- Weekly registrations bar chart --}}
    <div class="card">
        <div class="ch"><div class="ct">Weekly Registrations</div></div>
        <div class="cb">
            <div id="regChart" style="display: flex; justify-content: space-between; align-items: flex-end; height: 220px; padding: 20px 10px 10px 10px; width: 100%;"></div>
        </div>
    </div>

    {{-- User types donut --}}
    <div class="card">
        <div class="ch"><div class="ct">User Types</div></div>
        <div class="cb">
            <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap">
                <svg width="90" height="90" viewBox="0 0 42 42" style="flex-shrink:0">
                    <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="#1B3D6F" stroke-width="5"
                            stroke-dasharray="{{ $userTypes['contractor_pct'] }} {{ 100 - $userTypes['contractor_pct'] }}"
                            stroke-dashoffset="25"/>
                    <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="#F5874F" stroke-width="5"
                            stroke-dasharray="{{ $userTypes['subcontractor_pct'] }} {{ 100 - $userTypes['subcontractor_pct'] }}"
                            stroke-dashoffset="-{{ $userTypes['contractor_pct'] - 0 }}"/>
                    <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="#27AE60" stroke-width="5"
                            stroke-dasharray="{{ $userTypes['labour_pct'] }} {{ 100 - $userTypes['labour_pct'] }}"
                            stroke-dashoffset="-{{ $userTypes['contractor_pct'] + $userTypes['subcontractor_pct'] - 0 }}"/>
                    <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="#8E44AD" stroke-width="5"
                            stroke-dasharray="{{ $userTypes['apprentice_pct'] }} {{ 100 - $userTypes['apprentice_pct'] }}"
                            stroke-dashoffset="-{{ $userTypes['contractor_pct'] + $userTypes['subcontractor_pct'] + $userTypes['labour_pct'] - 0 }}"/>
                    <text x="21" y="24" text-anchor="middle" font-size="6" font-weight="800" fill="#1A2332">
                        {{ $stats['total_users'] }}
                    </text>
                </svg>
                <div class="dleg">
                    <div class="dleg-i"><div class="dleg-d" style="background:#1B3D6F"></div>Contractor<span class="dleg-p">{{ $userTypes['contractor_pct'] }}%</span></div>
                    <div class="dleg-i"><div class="dleg-d" style="background:#F5874F"></div>Sub-contractor<span class="dleg-p">{{ $userTypes['subcontractor_pct'] }}%</span></div>
                    <div class="dleg-i"><div class="dleg-d" style="background:#27AE60"></div>Labour<span class="dleg-p">{{ $userTypes['labour_pct'] }}%</span></div>
                    <div class="dleg-i"><div class="dleg-d" style="background:#8E44AD"></div>Apprentice<span class="dleg-p">{{ $userTypes['apprentice_pct'] }}%</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Activity + MRR --}}
<div class="g2">
    <div class="card">
        <div class="ch">
            <div class="ct">Recent Activity</div>
            {{-- <a href="{{ route('admin.activity') }}" class="btn btn-gh btn-sm">View all</a> --}}
        </div>
        <div class="cb" style="padding:0 18px">
            @forelse($recentActivity as $item)
                <div class="ai">
                    <div class="ad" style="background:{{ $item['color'] }}"></div>
                    <div style="flex:1">
                        <div class="at">{!! $item['text'] !!}</div>
                        <div class="ats">{{ $item['time'] }}</div>
                    </div>
                </div>
            @empty
                <div style="padding:20px;text-align:center;color:var(--grey);font-size:13px">No recent activity.</div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="ch"><div class="ct">Subscription MRR</div></div>
        <div class="cb" style="display:flex;flex-direction:column;gap:14px">
            @foreach($mrrBreakdown as $plan)
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:5px">
                        <span style="font-size:12.5px;font-weight:500">{{ $plan['name'] }}</span>
                        <span style="font-size:12.5px;font-weight:700;color:{{ $plan['color'] }}">{{ $plan['count'] }} subs</span>
                    </div>
                    <div class="pw"><div class="pb2" style="width:{{ $plan['pct'] }}%;background:{{ $plan['color'] }}"></div></div>
                </div>
            @endforeach
            <div style="border-top:1px solid var(--divider);padding-top:12px;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:13px;font-weight:600">Total MRR</span>
                <span style="font-size:18px;font-weight:800;color:var(--navy)">${{ number_format($stats['mrr']) }}</span>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Ensure a fallback array exists to prevent JavaScript execution crashes
        const regData = {!! json_encode($weeklyRegistrations ?? [0, 0, 0, 0, 0, 0, 0]) !!};
        const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        
        // Safely determine the highest registration count
        const max = regData.length > 0 ? Math.max(...regData) : 0;
        const chart = document.getElementById('regChart');
        
        if (chart) {
            chart.innerHTML = regData.map((val, i) => {
                // Ensure the height remains valid if max is 0
                const h = max > 0 ? Math.round((val / max) * 100) : 0;
                
                // Keep bars visible (e.g., 4%) even when count is 0 so the labels align perfectly at the base
                const barHeight = h > 0 ? h : 4; 
                
                // Color mapping highlight rules
                const bg = (val === max && max > 0) ? '#F5874F' : '#1B3D6F'; // --orange vs --navy hex fallbacks
                
                return `
                    <div class="bw" style="display: flex; flex-direction: column; align-items: center; flex: 1; height: 100%; justify-content: flex-end; margin: 0 4px;">
                        <div class="bar" style="height:${barHeight}%; background:${bg}; width: 100%; border-radius: 4px 4px 0 0; transition: height 0.3s ease;" title="${val} registrations"></div>
                        <div class="blbl" style="font-size: 11px; color: var(--grey, #64748B); margin-top: 8px; text-align: center;">${days[i]}</div>
                    </div>
                `;
            }).join('');
        }
    });
</script>
@endpush
{{-- @push('scripts')
<script>
    // Build bar chart from PHP data
    const regData = @json($weeklyRegistrations);
    const days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    const max  = Math.max(...regData);
    const chart = document.getElementById('regChart');
    if (chart) {
        chart.innerHTML = regData.map((val, i) => {
            const h  = max > 0 ? Math.round((val / max) * 100) : 0;
            const bg = i === regData.indexOf(max) ? 'var(--orange)' : 'var(--navy)';
            return `<div class="bw">
                <div class="bar" style="height:${h}%;background:${bg}" title="${val} registrations"></div>
                <div class="blbl">${days[i]}</div>
            </div>`;
        }).join('');
    }
</script>
@endpush --}}
