@if($menus->isNotEmpty())
<div class="field">
    <label for="dining_menu_id">เลือกชุดอาหารสำหรับโต๊ะ</label>
    <select class="input" id="dining_menu_id" name="dining_menu_id" required>
        <option value="">เลือกชุดอาหาร</option>
        @foreach($menus as $menu)
            <option value="{{ $menu->id }}">{{ $menu->name }}{{ $menu->description ? ' — '.$menu->description : '' }}</option>
        @endforeach
    </select>
</div>
@endif
