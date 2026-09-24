@extends('layouts.site')
@section('title', ($kind === 'table' ? 'จองโต๊ะจีน + คอนเสิร์ต' : 'ของที่ระลึก').' · 90th SISAT Reunion')
@section('content')
<section class="page-hero"><div class="shell"><div class="eyebrow">{{ $kind === 'table' ? 'Dinner & Concert' : 'Souvenir Shop' }}</div><h1 class="page-title">{{ $kind === 'table' ? 'จองโต๊ะจีน + ชมคอนเสิร์ต' : 'ของที่ระลึก 90 ปี' }}</h1><p class="lead">{{ $kind === 'table' ? 'หนึ่งโต๊ะรวมอาหารและสิทธิ์เข้าชมคอนเสิร์ต 8 ท่าน เลือกโซนและโต๊ะที่ต้องการได้จากผัง' : 'เลือกของที่ระลึก จองและส่งสลิป แล้วนำ QR มารับของในวันงาน' }}</p></div></section>
<section class="section" style="padding-top:20px"><div class="shell">
@if($kind === 'souvenir')
    @if($products->isEmpty()) <div class="empty">ยังไม่มีสินค้าเปิดขาย</div> @endif
    <div class="cards">@foreach($products as $product)
        <div class="card"><div class="card-art">@if($product->image_path)<img src="{{ route('products.image', $product) }}" alt="{{ $product->name }}">@else<span>{{ ['🎁','👕','☕','🎒'][$loop->index % 4] }}</span>@endif</div><div class="card-body"><span class="badge">ของที่ระลึก</span><h3>{{ $product->name }}</h3><p>{{ $product->description }}</p><div class="product-row"><div><div class="price">฿{{ number_format($product->price_satang / 100) }}</div><small class="muted">คงเหลือ {{ $product->available }} ชิ้น</small></div>
        @auth <form method="post" action="{{ route('orders.store') }}">@csrf<input type="hidden" name="kind" value="souvenir"><input type="hidden" name="resource_id" value="{{ $product->id }}"><input type="hidden" name="client_key" value="{{ (string) Str::uuid() }}"><label class="help" for="qty-{{ $product->id }}">จำนวน</label><input class="input" type="number" id="qty-{{ $product->id }}" name="quantity" min="1" max="10" value="1"><button class="btn btn-sm" type="submit" @disabled($product->available < 1)>จอง</button></form>
        @else <a class="btn btn-sm" href="{{ route('login') }}">เข้าสู่ระบบเพื่อจอง</a> @endauth</div></div></div>
    @endforeach</div>
@else
    <div class="booking-layout">
        <div class="panel venue-map"><div class="seat-stage">เวทีคอนเสิร์ต · CONCERT STAGE</div><div class="zone-map" aria-label="ผังโซนโต๊ะจีน">
            @foreach($zones as $zone)<a href="{{ route('shop.show', ['kind'=>'table','zone'=>$zone->code]) }}" class="zone-tile {{ $zone->code === $selectedZone?->code ? 'selected' : '' }} {{ !$zone->is_active ? 'closed' : '' }}" style="--zone-color:{{ $zone->color }}"><strong>{{ $zone->code }}</strong><span>โต๊ะ 01–60</span><small>{{ $zone->is_active ? '฿'.number_format($zone->price_satang / 100).' / โต๊ะ' : 'ยังไม่เปิดจอง' }}</small></a>@endforeach
        </div><div class="venue-entrance">↑ ทางเข้า</div><div class="seat-legend"><span><i class="dot available"></i> ว่าง</span><span><i class="dot held"></i> กำลังจอง</span><span><i class="dot sold"></i> จองแล้ว</span><span><i class="dot closed"></i> ยังไม่เปิด</span></div></div>
        <div class="panel table-selector"><div class="eyebrow">Zone {{ $selectedZone?->code }}</div><h2>เลือกโต๊ะ {{ $selectedZone?->code }}01–{{ $selectedZone?->code }}60</h2><p class="help">แถว 1 อยู่ใกล้เวที · โต๊ะละ 8 ท่าน · คลิกหมายเลขเพื่อดูรายละเอียด</p>
            <div class="table-grid-scroll"><div class="table-seat-grid">@foreach($places as $place)
                @php $state = !$selectedZone->is_active || !$place->is_active ? 'closed' : ($place->sold_by_order_id ? 'sold' : ($place->held_by_order_id ? 'held' : 'available')); @endphp
                @if($state === 'available')<a class="table-seat {{ $selectedTable?->id === $place->id ? 'selected' : '' }}" href="{{ route('shop.show', ['kind'=>'table','zone'=>$place->zone,'table'=>$place->label]) }}#reserve" title="โต๊ะ {{ $place->zone }}{{ $place->label }} ว่าง"><span>●</span>{{ $place->label }}</a>
                @else <span class="table-seat {{ $state }}" title="โต๊ะ {{ $place->zone }}{{ $place->label }} {{ ['held'=>'กำลังจอง','sold'=>'จองแล้ว','closed'=>'ยังไม่เปิด'][$state] }}"><span>●</span>{{ $place->label }}</span> @endif
            @endforeach</div></div>
            <div id="reserve" class="reserve-panel">@if($selectedTable && $selectedZone->is_active && $selectedTable->is_active && !$selectedTable->held_by_order_id && !$selectedTable->sold_by_order_id)
                <h3>โต๊ะ {{ $selectedZone->code }}{{ $selectedTable->label }} · ฿{{ number_format($selectedZone->price_satang / 100) }}</h3><p class="help">รวมอาหารและคอนเสิร์ตสำหรับ {{ $selectedTable->capacity }} ท่าน เมื่อยืนยันชำระเงินแล้วจะได้ QR {{ $selectedTable->capacity }} ใบ</p>
                @auth <form class="form" method="post" action="{{ route('orders.store') }}">@csrf<input type="hidden" name="kind" value="table"><input type="hidden" name="resource_id" value="{{ $selectedTable->id }}"><input type="hidden" name="quantity" value="1"><input type="hidden" name="client_key" value="{{ (string) Str::uuid() }}">@include('partials.menu-select')<div class="field"><label for="dietary_notes">อาหารที่แพ้ / ไม่รับประทาน (ถ้ามี)</label><textarea class="input" id="dietary_notes" name="dietary_notes" rows="2" maxlength="1000" placeholder="เช่น แพ้กุ้ง ไม่รับประทานเนื้อวัว"></textarea></div><button class="btn" type="submit">จองโต๊ะนี้</button></form>
                @else <a class="btn" href="{{ route('login') }}">เข้าสู่ระบบเพื่อจอง</a> @endauth
            @else <p class="muted">{{ $selectedZone?->is_active ? 'เลือกโต๊ะสีน้ำเงินที่ว่างเพื่อจอง' : 'โซนนี้ยังไม่เปิดจอง กรุณาเลือกโซนอื่น' }}</p> @endif</div>
        </div>
    </div>
@endif
</div></section>
@endsection
