@extends('layouts.site')
@section('title', 'บัญชีของฉัน · 90th SISAT Reunion')
@section('content')
<section class="page-hero"><div class="shell"><div class="eyebrow">MY ACCOUNT</div><h1 class="page-title">สวัสดี {{ auth()->user()->name }}</h1><p class="muted">ชื่อผู้ใช้ <strong>{{ auth()->user()->username ?: '#'.auth()->id() }}</strong> · ติดตามรายการและดู QR ของคุณได้ที่นี่</p></div></section>
<section class="section account-section"><div class="shell grid-2">
    <div class="panel"><div class="section-head"><div><div class="eyebrow">YOUR ORDERS</div><h2>รายการของฉัน</h2></div><span class="badge">{{ $orders->count() }} รายการ</span></div>
        <div class="order-list">@forelse($orders as $order)
            <a class="order-link" href="{{ route('orders.show', $order) }}"><span><strong>#{{ $order->id }} · {{ $order->kind === 'table' ? 'โต๊ะจีน + คอนเสิร์ต' : 'ของที่ระลึก' }}</strong><br><small class="muted">{{ $order->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</small></span><span class="badge">{{ ['awaiting_slip'=>'รอส่งสลิป','pending_review'=>'รอตรวจสอบ','changes_requested'=>'แก้ไขสลิป','approved'=>'อนุมัติแล้ว','redeemed'=>'ใช้สิทธิ์แล้ว','expired'=>'หมดเวลา','rejected'=>'ไม่ผ่านการตรวจ'][$order->status] ?? $order->status }}</span></a>
        @empty<div class="empty">ยังไม่มีรายการ เริ่มเลือกโต๊ะจีนหรือของที่ระลึกได้เลย</div>@endforelse</div>
    </div>
    <div class="panel"><div class="eyebrow">NEXT STEP</div><h2>ทำอะไรต่อดี?</h2><p class="muted">การจองโต๊ะจีนรวมอาหารและสิทธิ์เข้าชมคอนเสิร์ตแล้ว</p><div class="stack"><a class="btn" href="{{ route('shop.show', 'table') }}">เลือกโต๊ะจีน + คอนเสิร์ต →</a><a class="btn btn-ghost" href="{{ route('shop.show', 'souvenir') }}">เลือกของที่ระลึก</a></div>
        @if(auth()->user()->isStaff())<hr class="divider-line"><h3>เครื่องมือเจ้าหน้าที่</h3><div class="stack"><a class="btn btn-dark" href="/admin">แผงควบคุมเจ้าหน้าที่</a>@if(in_array(auth()->user()->role, ['finance','super_admin']))<a href="{{ route('finance.index') }}">ตรวจสลิป →</a>@endif @if(in_array(auth()->user()->role, ['scanner','shop_admin','super_admin']))<a href="{{ route('scan.index') }}">สแกน QR / จ่ายของ →</a>@endif @if(in_array(auth()->user()->role, ['support','super_admin']))<a href="{{ route('assist.index') }}">ช่วยลูกค้าสั่งซื้อ →</a>@endif</div>@endif
    </div>
</div></section>
@endsection
