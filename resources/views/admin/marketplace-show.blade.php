@extends('layouts.admin')
@section('page_title', $listing->title)
@section('content')

@php
    // Corrected to map exactly from the relation name property like the index table view does
    $catName   = $listing->category?->name ?? '';
    
    $catBadge = ['tools'=>'bn','equipment'=>'bo','materials'=>'bt','companies'=>'bp'][$catName] ?? 'bgr';
    $catLabel = ['tools'=>'Tools','equipment'=>'Equipment','materials'=>'Materials','companies'=>'Company'][$catName] ?? ucfirst($catName);
    $catEmoji = ['tools'=>'🔧','equipment'=>'🏗️','materials'=>'📦','companies'=>'🏢'][$catName] ?? '🏷️';
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
            <div class="dpav-w" style="text-align: center; margin-bottom: 20px;">
                @if($listing->images && $listing->images->isNotEmpty())
                    <div style="margin-bottom: 15px;">
                        <img src="{{ asset($listing->images->first()->path) }}" 
                             alt="{{ $listing->title }}" 
                             style="width: 120px; height: 120px; object-fit: cover; border-radius: var(--r, 8px); border: 1px solid var(--border, #e2e8f0); box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    </div>
                @else
                    <div style="font-size:56px; margin-bottom:12px; line-height: 1;">{{ $catEmoji }}</div>
                @endif
                
                <div class="dpn" style="font-size: 18px; font-weight: 600; color: var(--navy);">{{ $listing->title }}</div>
                <div class="dpr" style="color: var(--grey); font-size: 13px; margin-top: 2px;">Listed by {{ $listing->seller_name }}</div>
                <div style="display:flex;gap:6px;margin-top:12px;flex-wrap:wrap;justify-content:center">
                    <span class="bdg {{ $listing->is_active ? 'bg' : 'bgr' }}">{{ $listing->is_active ? 'Active' : 'Inactive' }}</span>
                    <span class="bdg {{ $catBadge }}">{{ $catLabel }}</span>
                </div>
            </div>

            <div class="dp-sec">Listing Info</div>
            <div class="dp-row"><span class="dpk">Price</span><span class="dpv" style="color:var(--navy);font-weight:700">${{ number_format($listing->price, 2) }}</span></div>
            <div class="dp-row"><span class="dpk">Category</span><span class="dpv">{{ $catLabel }}</span></div>
            <div class="dp-row"><span class="dpk">Condition</span><span class="dpv">{{ ucfirst($listing->condition_name) }}</span></div>
            <div class="dp-row"><span class="dpk">Location</span><span class="dpv">{{ $listing->location_name }}</span></div>
            <div class="dp-row"><span class="dpk">Listed Date</span><span class="dpv">{{ $listing->created_at->format('d M Y') }}</span></div>

            <div class="dp-sec">Seller Details</div>
            <div class="dp-row"><span class="dpk">Seller Name</span><span class="dpv">{{ $listing->owner->name }}</span></div>
            <div class="dp-row"><span class="dpk">Seller Email</span><span class="dpv">{{ $listing->owner->email ?? '—' }}</span></div>
            <div class="dp-row"><span class="dpk">Seller Type</span>
               <span class="dpv">
                    @php 
                        // 1. Get the value safely
                        $st = $listing->owner->user_type ?? null; 
                        
                        // 2. If it's an object (e.g., a relationship model), try to grab its underlying ID or string value
                        if (is_object($st)) {
                            $st = $st->id ?? $st->value ?? null;
                        }

                        $badges = [0 => 'bn', 1 => 'bo', 2 => 'bg', 3 => 'bp'];
                        $labels = [0 => 'Contractor', 1 => 'Sub-contractor', 2 => 'Labour', 3 => 'Apprentice'];
                    @endphp
                    
                    <span class="bdg {{ (is_scalar($st) && isset($badges[$st])) ? $badges[$st] : 'bgr' }}">
                        {{ (is_scalar($st) && isset($labels[$st])) ? $labels[$st] : 'Unknown Type' }}
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