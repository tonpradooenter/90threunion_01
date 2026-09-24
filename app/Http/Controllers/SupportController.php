<?php

namespace App\Http\Controllers;

use App\Models\BookablePlace;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportController extends Controller
{
    private function authorizeRole(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['support', 'super_admin'], true), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeRole($request);
        $identifier = trim((string) $request->query('identifier'));
        $customer = $identifier ? User::where('email', mb_strtolower($identifier))->orWhere('phone', preg_replace('/\D+/', '', $identifier))->first() : null;
        $zone = Zone::where('is_active', true)->where('code', (string) $request->query('zone', 'A'))->first();
        return view('support', [
            'customer' => $customer,
            'identifier' => $identifier,
            'products' => Product::where('kind', 'souvenir')->where('is_active', true)->get(),
            'zones' => Zone::where('is_active', true)->orderBy('sort_order')->get(),
            'zone' => $zone,
            'tables' => $zone ? BookablePlace::where('zone', $zone->code)->where('is_active', true)->whereNull('held_by_order_id')->whereNull('sold_by_order_id')->orderBy('label')->get() : collect(),
            'assistedOrders' => Order::where('actor_id', $request->user()->id)->whereColumn('actor_id', '!=', 'user_id')->latest()->limit(10)->get(),
        ]);
    }

    public function store(Request $request, OrderService $service)
    {
        $this->authorizeRole($request);
        $data = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'kind' => ['required', Rule::in(['souvenir', 'table'])],
            'resource_id' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1|max:10',
            'client_key' => 'required|uuid',
            'dietary_notes' => 'nullable|string|max:1000',
        ]);
        $customer = User::findOrFail($data['customer_id']);
        $order = $service->place($customer, $request->user(), $data['kind'], $data['resource_id'], $data['quantity'], $data['client_key'], $data['dietary_notes'] ?? null);
        return redirect()->route('orders.show', $order)->with('success', 'สร้างรายการให้ลูกค้าแล้ว รายการจะแสดงในบัญชีลูกค้าด้วย');
    }
}
