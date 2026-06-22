@extends('layouts.admin')
@section('page_title', $listing->title)
@section('content')

@php
    $catBadge = ['tools'=>'bn','equipment'=>'bo','materials'=>'bt','companies'=>'bp'][$listing->category] ?? 'bgr';
    $catLabel = ['tools'=>'Tools','equipment'=>'Equipment','materials'=>'Materials','companies'=>'Company'][$listing->category] ?? $listing->category;
    $catEmoji = ['tools'=>'🔧','equipment'=>'🏗️','materials'=>'🏠','companies'=>'🏢'][$listing->category] ?? '📦';
@endphp

<div class="ph">
    <div class="bc">
        Home <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <a href="{{ route('admin.marketplace') }}" style="color:var(--grey)">Marketplace</a>
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>{{ Str::limit($listing->title, 40) }}</span>
    </div>
    <div class="ph-row">
        <div>
            <div class="pt">{{ $listing->title }}</div>
            <div class="ps">Listed by {{ $listing->seller_name }} &mdash; {{ $listing->created_at->diffForHumans() }}</div>
        </div>
        <a href="{{ route('admin.marketplace') }}" class="btn btn-ol btn-sm">← Back</a>
    </div>
</div>

<div class="g2" style="align-items:start">

    <div class="card">
        <div class="ch"><div class="ct">Listing Details</div></div>
        <div class="cb">
            <div class="dpav-w">
                <div style="font-size:56px;margin-bottom:12px">{{ $catEmoji }}</div>
                <div class="dpn">{{ $listing->title }}</div>
                <div class="dpr">Listed by {{ $listing->seller_name }}</div>
                <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;justify-content:center">
                    <span class="bdg {{ $listing->is_active ? 'bg' : 'bgr' }}">{{ $listing->is_active ? 'Active' : 'Inactive' }}</span>
                    <span class="bdg {{ $catBadge }}">{{ $catLabel }}</span>
                </div>
            </div>

            <div class="dp-sec">Listing Info</div>
            <div class="dp-row"><span class="dpk">Price</span><span class="dpv" style="color:var(--navy);font-weight:700">${{ number_format($listing->price, 2) }}</span></div>
            <div class="dp-row"><span class="dpk">Category</span><span class="dpv">{{ $catLabel }}</span></div>
            <div class="dp-row"><span class="dpk">Condition</span><span class="dpv">{{ $listing->condition }}</span></div>
            <div class="dp-row"><span class="dpk">Location</span><span class="dpv">{{ $listing->location }}</span></div>
            <div class="dp-row"><span class="dpk">Listed Date</span><span class="dpv">{{ $listing->created_at->format('d M Y') }}</span></div>

            <div class="dp-sec">Seller Details</div>
            <div class="dp-row"><span class="dpk">Seller Name</span><span class="dpv">{{ $listing->seller_name }}</span></div>
            <div class="dp-row"><span class="dpk">Seller Email</span><span class="dpv">{{ $listing->seller->email ?? '—' }}</span></div>
            <div class="dp-row"><span class="dpk">Seller Type</span>
                <span class="dpv">
                    @php $st = $listing->seller->type ?? ''; @endphp
                    <span class="bdg {{ ['contractor'=>'bn','subcontractor'=>'bo','labour'=>'bg','apprentice'=>'bp'][$st] ?? 'bgr' }}">
                        {{ ['contractor'=>'Contractor','subcontractor'=>'Sub-contractor','labour'=>'Labour','apprentice'=>'Apprentice'][$st] ?? ucfirst($st) }}
                    </span>
                </span>
            </div>

            @if($listing->description)
                <div class="dp-sec">Description</div>
                <div style="font-size:13px;color:var(--grey);line-height:1.7;padding:8px 0">{{ $listing->description }}</div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="ch"><div class="ct">Admin Actions</div></div>
        <div class="cb" style="display:flex;flex-direction:column;gap:10px">
            @if($listing->is_active)
                <form method="POST" action="{{ route('admin.marketplace.deactivate', $listing->id) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-am" style="width:100%">Deactivate Listing</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.marketplace.activate', $listing->id) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-pr" style="width:100%">Activate Listing</button>
                </form>
            @endif

            <form method="POST" action="{{ route('admin.marketplace.flag', $listing->id) }}">
                @csrf @method('PATCH')
                <button class="btn btn-ol" style="width:100%">Flag for Review</button>
            </form>

            <form method="POST" action="{{ route('admin.marketplace.destroy', $listing->id) }}"
                  onsubmit="return confirm('Permanently remove this listing?')">
                @csrf @method('DELETE')
                <button class="btn btn-dn" style="width:100%">Remove Listing</button>
            </form>
        </div>
    </div>

</div>

@endsection
