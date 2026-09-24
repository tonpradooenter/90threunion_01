@extends('layouts.site')
@section('title', 'เข้าสู่ระบบ · 90th SISAT Reunion')
@section('content')
<section class="auth-section shell">
    <div class="auth-story">
        <span class="eyebrow">WELCOME HOME</span>
        <h1>กลับมาพบกัน<br><span class="gold">ในวาระ 90 ปี</span></h1>
        <p>เลือกโต๊ะจีน รับชมคอนเสิร์ต และติดตามของที่ระลึกได้ในบัญชีเดียว</p>
        <div class="auth-story-note"><strong>สำหรับเจ้าหน้าที่</strong><span>ใช้ชื่อผู้ใช้และรหัสผ่านที่ผู้ดูแลระบบมอบให้ จากนั้นเปิดเมนู “เจ้าหน้าที่”</span></div>
    </div>
    <div class="auth-card panel">
        <div class="eyebrow">SIGN IN</div>
        <h2>เข้าสู่ระบบ</h2>
        <p class="muted">กรอกชื่อผู้ใช้และรหัสผ่านของคุณ</p>
        <form class="form" method="post" action="{{ route('login') }}">@csrf
            <div class="field"><label for="username">ชื่อผู้ใช้</label><input class="input" id="username" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus placeholder="เช่น sisat90"></div>
            <div class="field"><label for="password">รหัสผ่าน</label><input class="input" type="password" id="password" name="password" required autocomplete="current-password" placeholder="กรอกรหัสผ่าน"></div>
            <button class="btn auth-submit" type="submit">เข้าสู่ระบบ <span aria-hidden="true">→</span></button>
        </form>
        <div class="auth-footer">ยังไม่มีบัญชี? <a href="{{ route('register') }}">สมัครสมาชิก</a></div>
    </div>
</section>
@endsection
