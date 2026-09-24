<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use chillerlan\QRCode\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class OrderController extends Controller
{
    public function store(Request $request, OrderService $service)
    {
        $data = $request->validate([
            'kind' => ['required', Rule::in(['souvenir', 'table'])],
            'resource_id' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1|max:10',
            'client_key' => 'required|uuid',
            'dietary_notes' => 'nullable|string|max:1000',
        ]);
        $order = $service->place($request->user(), $request->user(), $data['kind'], $data['resource_id'], $data['quantity'], $data['client_key'], $data['dietary_notes'] ?? null);
        return redirect()->route('orders.show', $order);
    }

    public function account(Request $request)
    {
        return view('account', ['orders' => Order::where('user_id', $request->user()->id)->latest()->get()]);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id || ($order->actor_id === $request->user()->id && in_array($request->user()->role, ['support', 'super_admin'], true)), 403);
        $order->load('items', 'places.place', 'admissionPasses');
        $ticketUrl = $order->ticket_code_encrypted ? route('scan.show', Crypt::decryptString($order->ticket_code_encrypted)) : null;
        $qr = $ticketUrl ? (new QRCode)->render($ticketUrl) : null;
        $passes = $order->admissionPasses->map(fn ($pass) => ['number' => $pass->guest_number, 'redeemed_at' => $pass->redeemed_at, 'qr' => (new QRCode)->render(route('scan.show', Crypt::decryptString($pass->token_encrypted)))]);
        return view('order', compact('order', 'qr', 'passes'));
    }

    public function slip(Request $request, Order $order, OrderService $service)
    {
        abort_unless($order->user_id === $request->user()->id || ($order->actor_id === $request->user()->id && in_array($request->user()->role, ['support', 'super_admin'], true)), 403);
        $request->validate(['slip' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120']);
        $path = $request->file('slip')->store('slips', 'private');
        try {
            $service->submitSlip($order, $request->user(), $path);
        } catch (Throwable $e) {
            Storage::disk('private')->delete($path);
            throw $e;
        }
        return redirect()->route('orders.show', $order)->with('success', 'ส่งหลักฐานการชำระเงินแล้ว รอเจ้าหน้าที่ตรวจสอบ');
    }

    public function slipFile(Request $request, Order $order)
    {
        abort_unless($order->slip_path && ($order->user_id === $request->user()->id || in_array($request->user()->role, ['finance', 'super_admin'], true) || ($request->user()->role === 'support' && $order->actor_id === $request->user()->id)), 403);
        return Storage::disk('private')->response($order->slip_path, 'payment-slip', ['X-Content-Type-Options' => 'nosniff', 'Content-Security-Policy' => 'sandbox']);
    }
}
