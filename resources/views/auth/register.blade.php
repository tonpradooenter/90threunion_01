@extends('layouts.site')
@section('title', 'สมัครสมาชิก · 90th SISAT Reunion')
@section('content')<div class="shell"><div class="auth-wrap panel"><div class="eyebrow">Join the Reunion</div><h1>สมัครสมาชิก</h1><p class="muted">กรอกเพียง 3 อย่าง จากนั้นเริ่มจองได้ทันที</p>
<form class="form" method="post" action="{{ route('register') }}">@csrf
    <div class="field"><label for="name">ชื่อที่ใช้ติดต่อ</label><input class="input" id="name" name="name" value="{{ old('name') }}" required autocomplete="name"></div>
    <div class="field"><label for="identifier">อีเมล หรือ เบอร์โทรศัพท์</label><input class="input" id="identifier" name="identifier" value="{{ old('identifier') }}" required autocomplete="username"><div class="help">ใช้อย่างใดอย่างหนึ่งก็ได้</div></div>
    <div class="field"><label for="password">ตั้งรหัสผ่าน (อย่างน้อย 10 ตัว)</label><input class="input" type="password" id="password" name="password" required autocomplete="new-password"></div>
    <div class="field"><label for="password_confirmation">ยืนยันรหัสผ่าน</label><input class="input" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"></div>
    <button class="btn" type="submit">สร้างบัญชี →</button>
</form><hr class="divider-line"><p class="help">มีบัญชีแล้ว? <a class="gold" href="{{ route('login') }}">เข้าสู่ระบบ</a></p></div></div>@endsection
