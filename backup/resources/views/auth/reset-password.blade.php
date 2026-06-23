<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>TradePro Admin — Set New Password</title>
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
        .auth-title { font-size:20px; font-weight:700; color:var(--slate); margin-bottom:4px; }
        .auth-sub { font-size:13px; color:var(--grey); margin-bottom:26px; line-height:1.6; }
        .form-group { margin-bottom:18px; }
        .form-label { display:block; font-size:12.5px; font-weight:600; color:var(--slate); margin-bottom:6px; }
        .pw-wrap { position:relative; }
        .form-control { width:100%; padding:10px 13px; border:1.5px solid var(--divider); border-radius:var(--r-sm); font-family:'Inter',sans-serif; font-size:13.5px; color:var(--slate); background:var(--white); outline:none; transition:border-color .15s, box-shadow .15s; }
        .form-control:focus { border-color:var(--navy); box-shadow:0 0 0 3px rgba(27,61,111,.08); }
        .form-control.is-invalid { border-color:var(--red); }
        .form-control::placeholder { color:var(--grey-l); }
        .pw-wrap .form-control { padding-right:38px; }
        .pw-toggle { position:absolute; right:11px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--grey); display:flex; align-items:center; padding:4px; }
        .pw-toggle:hover { color:var(--slate); }
        .form-error { font-size:12px; color:var(--red); margin-top:5px; }
        .pw-strength { margin-top:6px; }
        .pw-bar { height:4px; border-radius:4px; background:var(--divider); overflow:hidden; }
        .pw-bar-fill { height:100%; border-radius:4px; transition:width .3s, background .3s; width:0; }
        .pw-hint { font-size:11px; color:var(--grey); margin-top:4px; }
        .btn-full { width:100%; padding:11px; background:var(--navy); color:#fff; border:none; border-radius:var(--r-sm); font-family:'Inter',sans-serif; font-size:14px; font-weight:700; cursor:pointer; transition:background .15s; }
        .btn-full:hover { background:var(--navy-l); }
        .alert-error { background:var(--red-bg); border:1px solid #f5b7b1; color:var(--red); padding:10px 14px; border-radius:var(--r-sm); font-size:13px; margin-bottom:18px; }
        .back-link { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:20px; font-size:13px; color:var(--grey); text-decoration:none; }
        .back-link:hover { color:var(--navy); }
        .auth-footer { text-align:center; margin-top:22px; font-size:12px; color:var(--grey); }
    </style>
</head>
<body>
<div class="auth-wrap">

    <div class="auth-brand">
        <div class="brand-icon">T</div>
        <div class="brand-name">Trade<em>Pro</em></div>
    </div>

    <div class="auth-card">
        <div class="auth-title">Set new password</div>
        <div class="auth-sub">Choose a strong password for your admin account.</div>

        @if($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}"/>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $email ?? '') }}" required/>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <div class="pw-wrap">
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Minimum 8 characters" required autocomplete="new-password"
                           oninput="checkStrength(this.value)"/>
                    <button type="button" class="pw-toggle" onclick="togglePw('password')">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <div class="pw-strength">
                    <div class="pw-bar"><div class="pw-bar-fill" id="pwBar"></div></div>
                    <div class="pw-hint" id="pwHint">Enter a password</div>
                </div>
                @error('password')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <div class="pw-wrap">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control" placeholder="Repeat your password" required autocomplete="new-password"/>
                    <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation')">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-full">Reset Password</button>
        </form>

        <a href="{{ route('admin.login') }}" class="back-link">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to Sign In
        </a>
    </div>

    <div class="auth-footer">&copy; {{ date('Y') }} TradePro. All rights reserved.</div>
</div>

<script>
function togglePw(id) {
    const f = document.getElementById(id);
    f.type = f.type === 'password' ? 'text' : 'password';
}
function checkStrength(pw) {
    const bar  = document.getElementById('pwBar');
    const hint = document.getElementById('pwHint');
    let score = 0;
    if (pw.length >= 8)  score++;
    if (pw.length >= 12) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    const levels = [
        { w:'0%',   bg:'#E2E8F0', t:'Enter a password'  },
        { w:'25%',  bg:'#E74C3C', t:'Weak'               },
        { w:'50%',  bg:'#F39C12', t:'Fair'               },
        { w:'75%',  bg:'#F5874F', t:'Good'               },
        { w:'100%', bg:'#27AE60', t:'Strong ✓'           },
    ];
    const l = levels[Math.min(score, 4)];
    bar.style.width = l.w; bar.style.background = l.bg; hint.textContent = l.t;
}
</script>
</body>
</html>
