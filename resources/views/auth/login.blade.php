@extends('layouts.site')
@section('title', 'เข้าสู่ระบบ · 90th SISAT Reunion')
@section('content')<div class="shell"><div class="auth-wrap panel"><div class="eyebrow">Welcome Back</div><h1>เข้าสู่ระบบ</h1><p class="muted">เลือกวิธีที่คุณสะดวก บัญชีเดียวดูรายการทั้งหมดได้</p>
<div class="social">
    @if(config('services.line.client_id')) <a class="btn" style="background:#06c755;color:white" href="{{ route('social.redirect', 'line') }}">เข้าสู่ระบบด้วย LINE</a> @endif
    @if(config('services.google.client_id')) <a class="btn btn-ghost" href="{{ route('social.redirect', 'google') }}">เข้าสู่ระบบด้วย Google</a> @endif
</div>
<div class="divider">หรือใช้อีเมล / เบอร์โทร</div>
<form class="form" method="post" action="{{ route('login') }}">@csrf
    <div class="field"><label for="identifier">อีเมลหรือเบอร์โทรศัพท์</label><input class="input" id="identifier" name="identifier" value="{{ old('identifier') }}" required autocomplete="username"></div>
    <div class="field"><label for="password">รหัสผ่าน</label><input class="input" type="password" id="password" name="password" required autocomplete="current-password"></div>
    <button class="btn" type="submit">เข้าสู่ระบบ →</button>
</form><hr class="divider-line"><p class="help">ยังไม่มีบัญชี? <a class="gold" href="{{ route('register') }}">สมัครสมาชิก</a></p></div></div>@endsection
