<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#0b1730">
    <title>@yield('title', 'คืนสู่เหย้า 90 ปี SISAT')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<header class="topbar"><div class="shell nav">
    <a class="brand" href="{{ route('home') }}"><span class="brand-mark">90</span><span>คืนสู่เหย้า SISAT<small>วิทยาลัยเทคนิคศรีสะเกษ</small></span></a>
    <nav class="links" aria-label="เมนูหลัก">
        <a class="hide-mobile" href="{{ route('shop.show', 'souvenir') }}">ของที่ระลึก</a>
        <a class="hide-mobile" href="{{ route('shop.show', 'table') }}">โต๊ะจีน + คอนเสิร์ต</a>
        @auth
            <a href="{{ route('account') }}">รายการของฉัน</a>
            @if(auth()->user()->isStaff()) <a class="hide-mobile" href="/admin">เจ้าหน้าที่</a> @endif
            <form method="post" action="{{ route('logout') }}">@csrf <button class="btn btn-ghost btn-sm" type="submit">ออกจากระบบ</button></form>
        @else
            <a class="btn btn-sm" href="{{ route('login') }}">เข้าสู่ระบบ</a>
        @endauth
    </nav>
</div></header>
@if(session('success')) <div class="shell success" role="status">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="shell error" role="alert">{{ $errors->first() }}</div> @endif
<main>@yield('content')</main>
<footer class="footer"><div class="shell footer-inner"><span>90th SISAT Reunion · วิทยาลัยเทคนิคศรีสะเกษ</span><span>ระบบสาธิตสำหรับทดสอบขั้นตอนการใช้งาน</span></div></footer>
</body></html>
