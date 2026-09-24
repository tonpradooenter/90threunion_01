@extends('layouts.site')
@section('title', 'สมัครสมาชิก · 90th SISAT Reunion')
@section('content')
<section class="auth-section shell">
    <div class="auth-story">
        <span class="eyebrow">JOIN THE REUNION</span>
        <h1>เริ่มต้นง่าย ๆ<br><span class="gold">ในหนึ่งนาที</span></h1>
        <p>สมัครบัญชีเพื่อจองโต๊ะจีนพร้อมสิทธิ์เข้าชมคอนเสิร์ต เลือกซื้อของที่ระลึก และดูสถานะคำสั่งซื้อ</p>
        <div class="auth-story-note"><strong>จำชื่อผู้ใช้ของคุณไว้</strong><span>ใช้ชื่อเดียวกันนี้ทุกครั้งที่เข้าสู่ระบบ เจ้าหน้าที่สามารถค้นหาชื่อผู้ใช้นี้เพื่อช่วยเรื่องคำสั่งซื้อได้</span></div>
    </div>
    <div class="auth-card panel">
        <div class="eyebrow">CREATE ACCOUNT</div>
        <h2>สมัครสมาชิก</h2>
        <p class="muted">กรอกเพียงชื่อ ชื่อผู้ใช้ และรหัสผ่าน</p>
        <form class="form" method="post" action="{{ route('register') }}">@csrf
            <div class="field"><label for="name">ชื่อที่ให้เจ้าหน้าที่เรียก</label><input class="input" id="name" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="ชื่อ–นามสกุล"></div>
            <div class="field"><label for="username">ชื่อผู้ใช้</label><input class="input" id="username" name="username" value="{{ old('username') }}" required minlength="4" maxlength="50" pattern="[A-Za-z0-9._-]+" autocomplete="username" placeholder="เช่น sisat90"><div class="help">ใช้อังกฤษหรือตัวเลขอย่างน้อย 4 ตัว ใส่จุด ขีดกลาง หรือขีดล่างได้</div></div>
            <div class="field"><label for="password">รหัสผ่าน <span class="help">(อย่างน้อย 10 ตัว)</span></label><input class="input" type="password" id="password" name="password" required minlength="10" autocomplete="new-password"></div>
            <div class="field"><label for="password_confirmation">ยืนยันรหัสผ่าน</label><input class="input" type="password" id="password_confirmation" name="password_confirmation" required minlength="10" autocomplete="new-password"></div>
            <button class="btn auth-submit" type="submit">สร้างบัญชี <span aria-hidden="true">→</span></button>
        </form>
        <div class="auth-footer">มีบัญชีแล้ว? <a href="{{ route('login') }}">เข้าสู่ระบบ</a></div>
    </div>
</section>
@endsection
