<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>TradePro Admin — Reset Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}"/>
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
        .auth-wrap { width:100%; max-width:420px; }
        .auth-brand { display:flex; align-items:center; justify-content:center; gap:10px; margin-bottom:28px; }
        .brand-icon { width:40px; height:40px; background:var(--orange); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:800; color:#fff; }
        .brand-name { font-size:22px; font-weight:800; color:var(--navy-d); }
        .brand-name em { color:var(--orange); font-style:normal; }
        .auth-card { background:var(--white); border-radius:14px; padding:36px; box-shadow:0 4px 24px rgba(0,0,0,.09); border:1px solid var(--divider); }
        .auth-icon-wrap { width:56px; height:56px; background:var(--orange-l); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; }
        .auth-title { font-size:20px; font-weight:700; color:var(--slate); margin-bottom:6px; text-align:center; }
        .auth-sub { font-size:13px; color:var(--grey); margin-bottom:26px; text-align:center; line-height:1.6; }
        .form-group { margin-bottom:18px; }
        .form-label { display:block; font-size:12.5px; font-weight:600; color:var(--slate); margin-bottom:6px; }
        .form-control { width:100%; padding:10px 13px; border:1.5px solid var(--divider); border-radius:var(--r-sm); font-family:'Inter',sans-serif; font-size:13.5px; color:var(--slate); background:var(--white); outline:none; transition:border-color .15s, box-shadow .15s; }
        .form-control:focus { border-color:var(--navy); box-shadow:0 0 0 3px rgba(27,61,111,.08); }
        .form-control.is-invalid { border-color:var(--red); }
        .form-control::placeholder { color:var(--grey-l); }
        .form-error { font-size:12px; color:var(--red); margin-top:5px; }
        .btn-full { width:100%; padding:11px; background:var(--navy); color:#fff; border:none; border-radius:var(--r-sm); font-family:'Inter',sans-serif; font-size:14px; font-weight:700; cursor:pointer; transition:background .15s; }
        .btn-full:hover { background:var(--navy-l); }
        .alert { padding:10px 14px; border-radius:var(--r-sm); font-size:13px; margin-bottom:18px; }
        .alert-success { background:#E8F8F0; border:1px solid #a9dfbf; color:#1a7a4a; }
        .alert-error { background:var(--red-bg); border:1px solid #f5b7b1; color:var(--red); }
        .back-link { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:20px; font-size:13px; color:var(--grey); text-decoration:none; }
        .back-link:hover { color:var(--navy); }
        .auth-footer { text-align:center; margin-top:22px; font-size:12px; color:var(--grey); }
    </style>
</head>
<body>
<div class="auth-wrap">

    
    <div class="login-brand" style="text-align: center; margin-bottom: 24px;">
        <img src="{{ asset('images/logo.png') }}" alt="TradePro Logo" class="brand-logo" style="max-width: 180px; height: auto; object-fit: contain;">
    </div>

    <div class="auth-card">

        <div class="auth-icon-wrap">
            <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="var(--orange)" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>

        <div class="auth-title">Forgot your password?</div>
        <div class="auth-sub">
            Enter your admin email and we'll send you a link to reset your password.
        </div>

        @if(session('status'))
            <div class="alert alert-success">
                ✓ {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.email') }}">
            @csrf

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
                    autofocus
                />
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-full">
                Send Reset Link
            </button>
        </form>

        <a href="{{ route('admin.login') }}" class="back-link">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Back to Sign In
        </a>

    </div>

    <div class="auth-footer">&copy; {{ date('Y') }} TradePro. All rights reserved.</div>
</div>
</body>
</html>
