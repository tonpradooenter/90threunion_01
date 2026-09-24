@extends('layouts.site')
@section('content')
<section class="hero"><div class="shell hero-grid"><div>
    <div class="hero-kicker"><span class="hero-kicker-line"></span> 90 YEARS · SISAKET TECHNICAL COLLEGE</div>
    <h1>คืนสู่เหย้า<br><span class="gold">ครบรอบ 90 ปี</span></h1>
    <p class="lead">กลับมาพบเพื่อนและครูอีกครั้งในค่ำคืนแห่งความทรงจำ ร่วมโต๊ะอาหาร ชมคอนเสิร์ต และเก็บของที่ระลึกในโอกาสพิเศษนี้</p>
    <div class="hero-actions"><a class="btn" href="{{ route('shop.show', 'table') }}">เลือกโต๊ะจีน + คอนเสิร์ต <span aria-hidden="true">↗</span></a><a class="btn btn-ghost" href="{{ route('shop.show', 'souvenir') }}">ชมของที่ระลึก</a></div>
    <div class="hero-fact"><span class="hero-fact-number">600</span><span>โต๊ะ · 10 โซน<br>หนึ่งโต๊ะรองรับ 8 ท่าน</span><span class="hero-fact-divider"></span><span>จองโต๊ะครั้งเดียว<br>รวมอาหารและคอนเสิร์ต</span></div>
</div></div></section>
<section class="section home-discover"><div class="shell"><div class="section-head"><div><div class="eyebrow">EXPLORE THE REUNION</div><h2>เลือกประสบการณ์ของคุณ</h2><p class="muted">ทุกอย่างเริ่มต้นได้จากหน้านี้ เลือกสิ่งที่สนใจแล้วทำตามขั้นตอนง่าย ๆ</p></div></div>
<div class="experience-grid">
    <a class="experience-card experience-table" href="{{ route('shop.show', 'table') }}"><div class="experience-icon" aria-hidden="true">✦</div><div><span class="eyebrow">THE MAIN EVENT</span><h3>โต๊ะจีน + คอนเสิร์ต</h3><p>เลือกโซนและโต๊ะจากผังที่นั่ง พร้อมระบุชุดอาหารและข้อจำกัดด้านอาหาร</p><span class="experience-link">ดูผังโต๊ะและจอง <span aria-hidden="true">→</span></span></div><span class="experience-number">01</span></a>
    <a class="experience-card experience-gift" href="{{ route('shop.show', 'souvenir') }}"><div class="experience-icon" aria-hidden="true">◇</div><div><span class="eyebrow">TAKE A MEMORY HOME</span><h3>ของที่ระลึก 90 ปี</h3><p>เลือกสินค้าที่ชอบ จองไว้ล่วงหน้า แล้วรับของด้วย QR ในวันงาน</p><span class="experience-link">ดูของที่ระลึก <span aria-hidden="true">→</span></span></div><span class="experience-number">02</span></a>
</div></div></section>
<section class="section how-section"><div class="shell"><div class="section-head"><div><div class="eyebrow">HOW IT WORKS</div><h2>จองง่ายใน 3 ขั้นตอน</h2><p class="muted">ติดตามสถานะทุกอย่างได้ในบัญชีของคุณ</p></div><a class="btn btn-ghost" href="{{ route('register') }}">เริ่มสมัครสมาชิก →</a></div>
<div class="steps"><div class="step"><span>01</span><h3>สมัครและเลือก</h3><p>ใช้ชื่อผู้ใช้กับรหัสผ่าน จากนั้นเลือกโต๊ะหรือของที่ระลึก</p></div><div class="step"><span>02</span><h3>ส่งสลิปทดสอบ</h3><p>อัปโหลดหลักฐาน เจ้าหน้าที่การเงินจะตรวจและแจ้งสถานะ</p></div><div class="step"><span>03</span><h3>รับ QR ของคุณ</h3><p>เมื่อผ่านการตรวจ เปิด QR จากบัญชีเพื่อเข้างานหรือรับของ</p></div></div>
</div></section>
@endsection
