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
    <link
        rel="icon"
        type="image/svg+xml"
        href="{{ asset('images/logo.png') }}"
    />

    {{-- Global Admin CSS --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}"/>

    {{-- Page-specific CSS --}}
    <style>
        /* Add or update these rules in your layout's <style> tag */

        html, body {
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
        }

        /* Force the main container to limit its horizontal footprint */
        .main {
            min-width: 0;
            max-width: 100%;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Force the card body containing the table to contract on narrow viewports */
        .card, .cb {
            min-width: 0;
            max-width: 100%;
            width: 100%;
        }

        /* Upgrade your responsive class to bypass any flex container stretching */
        .table-responsive {
            display: block;
            width: 100%;
            max-width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        /* Ensure the table itself actually expands to trigger the scrollbar */
        .table-responsive table {
            width: 100%;
            min-width: 1200px !important; /* Adjust if your columns need more breathing room */
        }
    </style>
    @stack('styles')
    
</head>
<body>

    {{-- ── Sidebar ────────────────────────────────────────────── --}}
    <aside class="sb" id="sb">
       <div class="sb-brand" style="display: flex; align-items: center; gap: 10px;">
            <div class="sb-logo" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden;">
                <img src="{{ asset('images/logo.png') }}" 
                    alt="TradePro Logo" 
                    style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            {{-- <span class="sb-name">Trade<em>Pro</em></span> --}}
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

            <a href="#" 
               class="sb-item" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <span class="si">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </span>
                <span class="sl">Logout</span>
            </a>

            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </nav>

        <div class="sb-ft">
            {{-- 🔥 MODIFIÉ : Utilisation du guard admin --}}
            <div class="sb-av">{{ strtoupper(substr(auth()->guard('admin')->user()->name ?? 'A', 0, 1)) }}</div>
            <div class="sb-fi">
                <div class="sb-fn">{{ auth()->guard('admin')->user()->name ?? 'Admin' }}</div>
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

                {{-- 🔥 MODIFIÉ : Utilisation du guard admin --}}
                <div class="tb-av2" title="{{ auth()->guard('admin')->user()->name ?? 'Admin' }}">
                    {{ strtoupper(substr(auth()->guard('admin')->user()->name ?? 'A', 0, 1)) }}
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

    {{-- ── Alerte Flottante (Toast) ────────────────────────────── --}}
    @if (session('status') || session('success'))
        <div id="floating-alert" class="floating-alert">
            <div class="alert-content">
                <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="alert-message">{{ session('status') ?? session('success') }}</span>
            </div>
            <button onclick="closeAlert()" class="alert-close-btn">&times;</button>
        </div>
    @endif

    <style>
    .floating-alert {
        position: fixed;
        top: 25px;
        right: 25px;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 320px;
        max-width: 450px;
        background-color: #10b981;
        color: #ffffff;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 500;
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .floating-alert.show { transform: translateX(0); }
    .alert-content { display: flex; align-items: center; gap: 12px; }
    .alert-icon { width: 20px; height: 20px; flex-shrink: 0; }
    .alert-message { line-height: 1.4; }
    .alert-close-btn { background: none; border: none; color: rgba(255, 255, 255, 0.7); font-size: 22px; cursor: pointer; padding: 0 0 0 15px; line-height: 1; transition: color 0.2s ease; }
    .alert-close-btn:hover { color: #ffffff; }
    </style>

    {{-- Global admin JS --}}
    <script src="{{ asset('js/admin.js') }}"></script>

    {{-- Script pour la gestion du Toast et du Sidebar --}}
    <script>
        document.getElementById('sbTog').addEventListener('click', () => {
            document.getElementById('sb').classList.toggle('col');
        });

        document.addEventListener('DOMContentLoaded', function () {
            const alertBox = document.getElementById('floating-alert');
            if (alertBox) {
                setTimeout(() => { alertBox.classList.add('show'); }, 150);
                setTimeout(() => { closeAlert(); }, 4000);
            }
        });

        function closeAlert() {
            const alertBox = document.getElementById('floating-alert');
            if (alertBox) {
                alertBox.classList.remove('show');
                setTimeout(() => { alertBox.remove(); }, 400);
            }
        }
    </script>

    {{-- Page-specific JS --}}
    @stack('scripts')

</body>
</html>