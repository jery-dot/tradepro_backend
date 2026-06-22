@extends('layouts.admin')

@section('page_title', 'Marketplace')

@section('content')

<div class="ph">
    <div class="bc">
        Home
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Marketplace</span>
    </div>
    <div class="ph-row">
        <div>
            <div class="pt">Local Trade Marketplace</div>
            <div class="ps">{{ $totalCount }} listings — tools, equipment, materials and companies for sale or trade</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="ch">
        {{-- Category tabs --}}
        <div class="tabs">
            @foreach(['all'=>'All','tools'=>'Tools','equipment'=>'Equipment','materials'=>'Materials','companies'=>'Companies'] as $key => $label)
                <a href="{{ route('admin.marketplace', array_merge(request()->all(), ['category' => $key])) }}"
                   class="tab {{ request('category', 'all') === $key ? 'act' : '' }}">
                    {{ $label }} ({{ $categoryCounts[$key] ?? 0 }})
                </a>
            @endforeach
        </div>

        <div style="display:flex;gap:7px;align-items:center">
            <form method="GET" action="{{ route('admin.marketplace') }}">
                <input type="hidden" name="category" value="{{ request('category', 'all') }}">
                <select class="fsel" name="condition" onchange="this.form.submit()">
                    <option value="all"         {{ request('condition','all') === 'all'         ? 'selected':'' }}>All Conditions</option>
                    <option value="new"         {{ request('condition') === 'new'               ? 'selected':'' }}>New</option>
                    <option value="used"        {{ request('condition') === 'used'              ? 'selected':'' }}>Used</option>
                    <option value="refurbished" {{ request('condition') === 'refurbished'       ? 'selected':'' }}>Refurbished</option>
                </select>
            </form>
            <a href="{{ route('admin.marketplace.export', request()->all()) }}" class="btn btn-ol btn-sm">Export</a>
        </div>
    </div>

    <div class="cb" style="padding-bottom:0">
        <form method="GET" action="{{ route('admin.marketplace') }}" class="tbar">
            <input type="hidden" name="category"  value="{{ request('category', 'all') }}">
            <input type="hidden" name="condition" value="{{ request('condition', 'all') }}">
            <div class="srch">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" placeholder="Search listings, seller..."
                       value="{{ request('search') }}"/>
            </div>
        </form>

        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox"/></th>
                        <th>Listing</th>
                        <th>Category</th>
                        <th>Seller</th>
                        <th>Price</th>
                        <th>Condition</th>
                        <th>Location</th>
                        <th>Listed</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listings as $listing)
                       @php
                            // Fetch the name property safely (returns null if relation is empty)
                            $catName   = $listing->category?->name; 

                            $catBadge  = ['tools' => 'bn', 'equipment' => 'bo', 'materials' => 'bt', 'companies' => 'bp'][$catName] ?? 'bgr';
                            $catLabel  = ['tools' => 'Tools', 'equipment' => 'Equipment', 'materials' => 'Materials', 'companies' => 'Company'][$catName] ?? $catName;
                            $catEmoji  = ['tools' => '🔧', 'equipment' => '🏗️', 'materials' => '📦', 'companies' => '🏢'][$catName] ?? '🏷️';
                        @endphp
                        <tr>
                            <td><input type="checkbox" value="{{ $listing->id }}"/></td>
                            <td>
                                <div class="uc" style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        @if($listing->images->isNotEmpty())
                                            <img src="{{ asset($listing->images->first()->path) }}" 
                                                alt="{{ $listing->title }}" 
                                                style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;">
                                        @else
                                            <img src="{{ asset('images/placeholder.avif') }}" 
                                            alt="No Image Available" 
                                            style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;">
                                        @endif
                                    </div>
                                    
                                    <div class="un" style="font-size: 13px;">{{ $listing->title }}</div>
                                </div>
                            </td>
                            <td><span class="bdg {{ $catBadge }}">{{ $catLabel }}</span></td>
                            <td style="font-size:12.5px;color:var(--grey)">{{ $listing->seller_name }}</td>
                            <td style="font-size:13px;font-weight:700;color:var(--navy)">${{ number_format($listing->price, 2) }}</td>
                            <td style="font-size:12.5px;color:var(--grey)">{{ $listing->condition }}</td>
                            <td style="font-size:12.5px;color:var(--grey)">{{ $listing->location }}</td>
                            <td style="font-size:12.5px;color:var(--grey)">{{ $listing->created_at->format('d M Y') }}</td>
                            <td>
                                <span class="bdg {{ $listing->is_active ? 'bg' : 'bgr' }}">
                                    {{ $listing->is_active ? 'active' : 'inactive' }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <a href="{{ route('admin.marketplace.show', $listing->id) }}"
                                       class="btn btn-ol btn-xs">View</a>
                                    <form method="POST"
                                          action="{{ route('admin.marketplace.destroy', $listing->id) }}"
                                          onsubmit="return confirm('Remove this listing?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-dn btn-xs">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div style="padding:40px;text-align:center;color:var(--grey)">No listings found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pag">
        <div class="pi">Showing {{ $listings->firstItem() }}–{{ $listings->lastItem() }} of {{ $listings->total() }}</div>
        <div class="pbs">
            @if($listings->onFirstPage())
                <button class="pb" disabled>‹</button>
            @else
                <a href="{{ $listings->previousPageUrl() }}" class="pb">‹</a>
            @endif
            @foreach($listings->getUrlRange(1, min($listings->lastPage(), 7)) as $page => $url)
                <a href="{{ $url }}" class="pb {{ $listings->currentPage() === $page ? 'act' : '' }}">{{ $page }}</a>
            @endforeach
            @if($listings->hasMorePages())
                <a href="{{ $listings->nextPageUrl() }}" class="pb">›</a>
            @else
                <button class="pb" disabled>›</button>
            @endif
        </div>
    </div>
</div>

@endsection
