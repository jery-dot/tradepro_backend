<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>TradePro — @yield('page_title', 'Admin Panel')</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>

    {{-- Global Admin CSS --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}"/>

    {{-- Page-specific CSS --}}
    @stack('styles')
</head>
<body>

    {{-- ── Sidebar ────────────────────────────────────────────── --}}
    <aside class="sb" id="sb">
        <div class="sb-brand">
            <div class="sb-logo">T</div>
            <span class="sb-name">Trade<em>Pro</em></span>
        </div>

        <nav class="sb-nav">
            <div class="sb-sec">Overview</div>
            <a href="{{ route('admin.dashboard') }}"
               class="sb-item {{ request()->routeIs('admin.dashboard') ? 'act' : '' }}">
                <span class="si">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </span>
                <span class="sl">Dashboard</span>
            </a>

            <div class="sb-sec">Management</div>

            <a href="{{ route('admin.users') }}"
               class="sb-item {{ request()->routeIs('admin.users*') ? 'act' : '' }}">
                <span class="si">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </span>
                <span class="sl">Users</span>
                <span class="sb-bdg">{{ $userCount ?? 0 }}</span>
            </a>

            <a href="{{ route('admin.jobs') }}"
               class="sb-item {{ request()->routeIs('admin.jobs*') ? 'act' : '' }}">
                <span class="si">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2"/>
                        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                        <line x1="12" y1="12" x2="12" y2="16"/>
                        <line x1="10" y1="14" x2="14" y2="14"/>
                    </svg>
                </span>
                <span class="sl">Jobs</span>
                <span class="sb-bdg">{{ $jobCount ?? 0 }}</span>
            </a>

            <a href="{{ route('admin.apprenticeships') }}"
               class="sb-item {{ request()->routeIs('admin.apprenticeships*') ? 'act' : '' }}">
                <span class="si">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </span>
                <span class="sl">Apprenticeships</span>
                <span class="sb-bdg">{{ $apprenticeCount ?? 0 }}</span>
            </a>

            <a href="{{ route('admin.marketplace') }}"
               class="sb-item {{ request()->routeIs('admin.marketplace*') ? 'act' : '' }}">
                <span class="si">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </span>
                <span class="sl">Marketplace</span>
            </a>

            <a href="{{ route('admin.reviews') }}"
               class="sb-item {{ request()->routeIs('admin.reviews*') ? 'act' : '' }}">
                <span class="si">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                </span>
                <span class="sl">Reviews</span>
            </a>

            <div class="sb-sec">Platform</div>

            <a href="{{ route('admin.subscriptions') }}"
               class="sb-item {{ request()->routeIs('admin.subscriptions*') ? 'act' : '' }}">
                <span class="si">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2"/>
                        <line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                </span>
                <span class="sl">Subscriptions</span>
            </a>

            <!-- <a href="{{ route('admin.settings') }}"
               class="sb-item {{ request()->routeIs('admin.settings*') ? 'act' : '' }}">
                <span class="si">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>
                    </svg>
                </span>
                <span class="sl">Settings</span>
            </a> -->
        </nav>

        <div class="sb-ft">
            <div class="sb-av">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
            <div class="sb-fi">
                <div class="sb-fn">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="sb-fr">Super Admin</div>
            </div>
        </div>
    </aside>

    {{-- ── Main ────────────────────────────────────────────────── --}}
    <div class="main">

        {{-- Topbar --}}
        <div class="topbar">
            <button class="tb-tog" id="sbTog" aria-label="Toggle sidebar">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>

            <div class="tb-ttl">@yield('page_title', 'Dashboard')</div>

            <form class="tb-srch" method="GET" action="{{ route('admin.search') }}">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="q" placeholder="Search TradePro..."
                       value="{{ request('q') }}"/>
            </form>

            <div class="tb-acts">
                <button class="ib" aria-label="Notifications">
                    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="ndot"></span>
                </button>

                <div class="tb-av2" title="{{ auth()->user()->name ?? 'Admin' }}">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
            </div>
        </div>

        {{-- Page Content --}}
        <div class="pg">
            @yield('content')
        </div>

    </div>{{-- end .main --}}

    {{-- ── Shared overlays ──────────────────────────────────────── --}}

    {{-- Detail slide panel --}}
    <div class="dov" id="dov">
        <div class="dpanel">
            <div class="dph">
                <div class="dpt" id="dpT">Details</div>
                <button class="dpc" onclick="closeDp()" aria-label="Close">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="dpb" id="dpB"></div>
        </div>
    </div>

    {{-- Confirm modal --}}
    <div class="mov" id="mov">
        <div class="modal">
            <h3 id="mH">Confirm</h3>
            <p id="mB">Are you sure?</p>
            <div class="modal-acts">
                <button class="btn btn-ol" onclick="closeMov()">Cancel</button>
                <button class="btn btn-dn" id="mConf">Confirm</button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div class="toast" id="toast"></div>

    {{-- Global admin JS --}}
    <script src="{{ asset('js/admin.js') }}"></script>

    {{-- Sidebar toggle (runs after admin.js) --}}
    <script>
        document.getElementById('sbTog').addEventListener('click', () => {
            document.getElementById('sb').classList.toggle('col');
        });
    </script>

    {{-- Page-specific JS --}}
    @stack('scripts')

</body>
</html>
