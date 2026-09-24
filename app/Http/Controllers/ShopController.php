<?php

namespace App\Http\Controllers;

use App\Models\BookablePlace;
use App\Models\Product;
use App\Models\Zone;
use App\Services\OrderService;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function home()
    {
        return view('home', [
            'souvenirs' => Product::where('kind', 'souvenir')->where('is_active', true)->limit(3)->get(),
            'tableCount' => BookablePlace::where('kind', 'table')->whereNull('held_by_order_id')->whereNull('sold_by_order_id')->count(),
        ]);
    }

    public function show(Request $request, string $kind, OrderService $orders)
    {
        abort_unless(in_array($kind, ['souvenir', 'table'], true), 404);
        $orders->sweepIfDue();
        $zones = $kind === 'table' ? Zone::orderBy('sort_order')->get() : collect();
        $selectedZone = $zones->firstWhere('code', $request->query('zone')) ?: $zones->first();
        $places = $selectedZone ? BookablePlace::where('kind', 'table')->where('zone', $selectedZone->code)->orderBy('label')->get() : collect();
        $selectedTable = $places->firstWhere('label', $request->query('table'));
        return view('shop', [
            'kind' => $kind,
            'products' => $kind === 'souvenir' ? Product::where('kind', 'souvenir')->where('is_active', true)->orderBy('id')->get() : collect(),
            'zones' => $zones,
            'selectedZone' => $selectedZone,
            'places' => $places,
            'selectedTable' => $selectedTable,
        ]);
    }
}
