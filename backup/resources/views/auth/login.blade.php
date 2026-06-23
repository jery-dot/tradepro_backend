<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>TradePro Admin — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 14px; }
        body {
            font-family: 'Inter', sans-serif;
            background: #F0F2F6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        :root {
            --navy:    #1B3D6F;
            --navy-d:  #0F2548;
            --navy-l:  #2A5298;
            --orange:  #F5874F;
            --orange-d:#E06E35;
            --orange-l:#FDF0EA;
            --green:   #27AE60;
            --red:     #E74C3C;
            --red-bg:  #FDEDEC;
            --slate:   #1A2332;
            --grey:    #64748B;
            --grey-l:  #94A3B8;
            --grey-bg: #F8FAFC;
            --divider: #E2E8F0;
            --white:   #FFFFFF;
            --r:       10px;
            --r-sm:    6px;
        }

        .login-wrap {
            width: 100%;
            max-width: 440px;
        }

        /* Logo area */
        .login-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 28px;
        }
        .brand-icon {
            width: 40px; height: 40px;
            background: var(--orange);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 800; color: #fff;
        }
        .brand-name {
            font-size: 22px;
            font-weight: 800;
            color: var(--navy-d);
        }
        .brand-name em { color: var(--orange); font-style: normal; }

        /* Card */
        .login-card {
            background: var(--white);
            border-radius: 14px;
            padding: 36px 36px 28px;
            box-shadow: 0 4px 24px rgba(0,0,0,.09);
            border: 1px solid var(--divider);
        }

        .login-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--slate);
            margin-bottom: 4px;
        }
        .login-sub {
            font-size: 13px;
            color: var(--grey);
            margin-bottom: 28px;
        }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--slate);
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid var(--divider);
            border-radius: var(--r-sm);
            font-family: 'Inter', sans-serif;
            font-size: 13.5px;
            color: var(--slate);
            background: var(--white);
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(27,61,111,.08);
        }
        .form-control.is-invalid { border-color: var(--red); }
        .form-control::placeholder { color: var(--grey-l); }

        .form-error {
            font-size: 12px;
            color: var(--red);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Password row */
        .pw-wrap { position: relative; }
        .pw-toggle {
            position: absolute;
            right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; color: var(--grey);
            display: flex; align-items: center;
            padding: 4px;
        }
        .pw-toggle:hover { color: var(--slate); }
        .pw-wrap .form-control { padding-right: 38px; }

        /* Remember / Forgot row */
        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }
        .check-wrap {
            display: flex; align-items: center; gap: 7px;
            font-size: 12.5px; color: var(--grey);
            cursor: pointer; user-select: none;
        }
        .check-wrap input[type="checkbox"] { cursor: pointer; accent-color: var(--navy); }
        .forgot-link {
            font-size: 12.5px;
            color: var(--navy);
            font-weight: 500;
            text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 11px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: var(--r-sm);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover { background: var(--navy-l); }
        .btn-login:disabled { opacity: .7; cursor: not-allowed; }

        /* Alert */
        .alert {
            padding: 10px 14px;
            border-radius: var(--r-sm);
            font-size: 13px;
            margin-bottom: 18px;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .alert-error {
            background: var(--red-bg);
            border: 1px solid #f5b7b1;
            color: var(--red);
        }
        .alert-success {
            background: #E8F8F0;
            border: 1px solid #a9dfbf;
            color: #1a7a4a;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 12px;
            color: var(--grey);
        }

        .divider-line {
            border: none;
            border-top: 1px solid var(--divider);
            margin: 22px 0 18px;
        }

        /* Dots decoration */
        .login-bg-badge {
            text-align: center;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--grey-l);
        }
    </style>
</head>
<body>
<div class="login-wrap">

    {{-- Brand --}}
    <div class="login-brand">
        <div class="brand-icon">T</div>
        <div class="brand-name">Trade<em>Pro</em></div>
    </div>

    <div class="login-card">

        <div class="login-bg-badge">Admin Portal</div>
        <div class="login-title">Welcome back</div>
        <div class="login-sub">Sign in to your admin account</div>

        {{-- Session / Auth Errors --}}
        @if(session('error'))
            <div class="alert alert-error">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if(session('status'))
            <div class="alert alert-success">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="{{ route('admin.login.submit') }}" id="loginForm">
            @csrf

            {{-- Email --}}
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="admin@tradepro.com"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    autofocus
                />
                @error('email')
                    <div class="form-error">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="pw-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    />
                    <button type="button" class="pw-toggle" id="pwToggle" aria-label="Toggle password">
                        <svg id="eyeIcon" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div class="form-error">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Remember + Forgot --}}
            <div class="form-row">
                <label class="check-wrap">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}/>
                    Remember me
                </label>
                <a href="{{ route('admin.password.request') }}" class="forgot-link">
                    Forgot password?
                </a>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-login" id="loginBtn">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Sign In to Admin Panel
            </button>
        </form>

    </div>

    <div class="login-footer">
        &copy; {{ date('Y') }} TradePro. All rights reserved.
        &nbsp;·&nbsp;
        <a href="#" style="color:var(--grey)">Privacy Policy</a>
    </div>

</div>

<script>
    // Password toggle
    document.getElementById('pwToggle').addEventListener('click', () => {
        const pw   = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            pw.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    });

    // Loading state on submit
    document.getElementById('loginForm').addEventListener('submit', () => {
        const btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Signing in...';
    });
</script>

<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
</body>
</html>
