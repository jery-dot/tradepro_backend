@extends('layouts.admin')

@section('page_title', 'Reviews & Ratings')

@section('content')

<div class="ph">
    <div class="bc">
        Home
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Reviews & Ratings</span>
    </div>
    <div class="ph-row">
        <div>
            <div class="pt">Reviews &amp; Ratings</div>
            <div class="ps">Platform-wide job review management</div>
        </div>
    </div>
</div>

{{-- Stat cards --}}
<div class="sg" style="grid-template-columns:repeat(4,1fr)">
    <div class="sc">
        <div class="sci" style="background:var(--amber-bg)">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--amber)" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
        </div>
        <div>
            <div class="sv">{{ number_format($stats['avg_rating'], 1) }}</div>
            <div class="slb">Avg Platform Rating</div>
        </div>
    </div>
    <div class="sc">
        <div class="sci" style="background:var(--green-bg)">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--green)" stroke-width="2">
                <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/>
                <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
            </svg>
        </div>
        <div>
            <div class="sv">{{ number_format($stats['total_reviews']) }}</div>
            <div class="slb">Total Reviews</div>
        </div>
    </div>
    {{-- <div class="sc">
        <div class="sci" style="background:var(--red-bg)">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--red)" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div>
            <div class="sv">{{ $stats['flagged_reviews'] }}</div>
            <div class="slb">Flagged Reviews</div>
        </div>
    </div> --}}
    {{-- <div class="sc">
        <div class="sci" style="background:#e8edf8">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--navy)" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <div>
            <div class="sv">{{ $stats['recommend_pct'] }}%</div>
            <div class="slb">Would Recommend</div>
        </div>
    </div> --}}
</div>

<div class="card">
    <div class="ch">
        <div class="ct">All Reviews</div>
        <form method="GET" action="{{ route('admin.reviews') }}" style="display:flex;gap:7px">
            <select class="fsel" name="rating" onchange="this.form.submit()">
                <option value="all">All Ratings</option>
                <option value="5" {{ request('rating') === '5' ? 'selected':'' }}>5 Stars</option>
                <option value="4" {{ request('rating') === '4' ? 'selected':'' }}>4 Stars</option>
                <option value="3" {{ request('rating') === '3' ? 'selected':'' }}>3 Stars</option>
                <option value="low" {{ request('rating') === 'low' ? 'selected':'' }}>1–2 Stars</option>
            </select>
            <select class="fsel" name="type" onchange="this.form.submit()">
                <option value="all">All Types</option>
                <option value="contractor"    {{ request('type') === 'contractor'    ? 'selected':'' }}>Contractor</option>
                <option value="subcontractor" {{ request('type') === 'subcontractor' ? 'selected':'' }}>Sub-contractor</option>
                <option value="labour"        {{ request('type') === 'labour'        ? 'selected':'' }}>Labour</option>
            </select>
        </form>
    </div>

    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th>Reviewer</th>
                    <th>Reviewed</th>
                    <th>Overall</th>
                    <th>Communication</th>
                    <th>Job Quality</th>
                    <th>Payment</th>
                    <th>Work Env.</th>
                    <th>Recommend</th>
                    <th>Comments</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    @php
                        $typeBadge = ['contractor'=>'bn','subcontractor'=>'bo','labour'=>'bg'][$review->reviewed_type] ?? 'bgr';
                        $typeLabel = ['contractor'=>'Contractor','subcontractor'=>'Sub-contractor','labour'=>'Labour'][$review->reviewed_type] ?? $review->reviewed_type;
                        $starsHtml = str_repeat('★', $review->overall_rating) . str_repeat('☆', 5 - $review->overall_rating);
                    @endphp
                    <tr>
                        <td style="font-size:12.5px;font-weight:600">{{ $review->reviewer_name }}</td>
                        <td style="font-size:12.5px;color:var(--grey)">
                            {{ $review->reviewed_name }}
                            <br>
                            <span class="bdg {{ $typeBadge }}" style="font-size:10px">{{ $typeLabel }}</span>
                        </td>
                        <td>
                            <span style="color:var(--amber)">{{ $starsHtml }}</span>
                            <span style="font-size:11.5px;font-weight:600"> {{ $review->overall_rating }}.0</span>
                        </td>
                        <td style="text-align:center;font-size:12px;font-weight:600">{{ $review->communication_rating }}/5</td>
                        <td style="text-align:center;font-size:12px;font-weight:600">{{ $review->job_quality_rating }}/5</td>
                        <td style="text-align:center;font-size:12px;font-weight:600">{{ $review->payment_timeliness_rating }}/5</td>
                        <td style="text-align:center;font-size:12px;font-weight:600">{{ $review->work_environment_rating }}/5</td>
                        <td style="text-align:center">
                            <span class="bdg {{ $review->would_recommend ? 'bg' : 'br' }}">
                                {{ $review->would_recommend ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--grey);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                            title="{{ $review->comment }}">
                            {{ $review->comment }}
                        </td>
                        <td style="font-size:12px;color:var(--grey-l)">{{ $review->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display:flex;gap:4px">
                                <a href="{{ route('admin.reviews.show', $review->id) }}"
                                   class="btn btn-ol btn-xs">View</a>
                                <form method="POST"
                                      action="{{ route('admin.reviews.destroy', $review->id) }}"
                                      onsubmit="return confirm('Remove this review?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-dn btn-xs">Remove</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">
                            <div style="padding:40px;text-align:center;color:var(--grey)">No reviews found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pag">
        <div class="pi">Showing {{ $reviews->firstItem() }}–{{ $reviews->lastItem() }} of {{ $reviews->total() }}</div>
        <div class="pbs">
            @if($reviews->onFirstPage())
                <button class="pb" disabled>‹</button>
            @else
                <a href="{{ $reviews->previousPageUrl() }}" class="pb">‹</a>
            @endif
            @foreach($reviews->getUrlRange(1, min($reviews->lastPage(), 7)) as $page => $url)
                <a href="{{ $url }}" class="pb {{ $reviews->currentPage() === $page ? 'act' : '' }}">{{ $page }}</a>
            @endforeach
            @if($reviews->hasMorePages())
                <a href="{{ $reviews->nextPageUrl() }}" class="pb">›</a>
            @else
                <button class="pb" disabled>›</button>
            @endif
        </div>
    </div>
</div>

@endsection
