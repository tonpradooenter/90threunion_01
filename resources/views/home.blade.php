@extends('layouts.site')
@section('content')
<section class="hero"><div class="shell hero-grid"><div>
    <div class="eyebrow">Sisaket Technical College · 90 Years</div>
    <h1>คิดถึงวันวาน<br><span class="gold">กลับมาพบกัน</span></h1>
    <p class="lead">งานคืนสู่เหย้าครบรอบ 90 ปี วิทยาลัยเทคนิคศรีสะเกษ ร่วมฉลองความทรงจำดี ๆ ผ่านเสียงเพลง มื้อพิเศษ และของที่ระลึกที่เก็บไว้ได้นาน</p>
    <div class="hero-actions"><a class="btn" href="{{ route('shop.show', 'table') }}">จองโต๊ะจีน + เข้าชมคอนเสิร์ต →</a><a class="btn btn-ghost" href="{{ route('shop.show', 'souvenir') }}">ดูของที่ระลึก</a></div>
</div><div class="hero-art" aria-hidden="true"><div class="medallion"><div><strong>90</strong><span>YEARS OF MEMORIES</span></div></div></div></div></section>
<section class="section"><div class="shell"><div class="section-head"><div><div class="eyebrow">Celebrate Together</div><h2>ทุกอย่างพร้อมในที่เดียว</h2><p class="muted">เลือก จอง ส่งหลักฐาน แล้วติดตามสถานะในบัญชีของคุณ</p></div></div>
<div class="cards">
    <a class="card" href="{{ route('shop.show', 'souvenir') }}"><div class="card-art"><span>🎁</span></div><div class="card-body"><span class="badge">เก็บความทรงจำ</span><h3>ของที่ระลึก</h3><p>เลือกของฝากที่คุณชอบ จองออนไลน์และรับที่งานด้วย QR</p><span class="gold">ดูสินค้า →</span></div></a>
    <a class="card" href="{{ route('shop.show', 'table') }}"><div class="card-art"><span>🍽️</span></div><div class="card-body"><span class="badge">อาหาร + คอนเสิร์ต</span><h3>โต๊ะจีนและเข้างาน</h3><p>จองหนึ่งโต๊ะ ได้สิทธิ์รับประทานอาหารและชมคอนเสิร์ตตามจำนวนที่นั่งของโต๊ะ</p><span class="gold">เลือกโต๊ะ →</span></div></a>
</div></div></section>
<section class="section" style="background:#10213b"><div class="shell grid-2"><div><div class="eyebrow">Simple & Comfortable</div><h2 class="page-title">จองง่ายใน 3 ขั้นตอน</h2><p class="lead">สมัครด้วยอีเมลหรือเบอร์โทร เลือกรายการ แล้วอัปโหลดสลิป เจ้าหน้าที่ตรวจสอบก่อนออก QR สำหรับวันงาน</p></div><div class="stat-row"><div class="stat"><strong>01</strong><small>เลือกสินค้า / ที่นั่ง / โต๊ะ</small></div><div class="stat"><strong>02</strong><small>ส่งหลักฐานการโอน</small></div><div class="stat"><strong>03</strong><small>รับ QR ในบัญชี</small></div></div></div></section>
@endsection
