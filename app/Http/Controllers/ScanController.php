<?php

namespace App\Http\Controllers;

use App\Models\AdmissionPass;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    private function authorizeRole(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['scanner', 'shop_admin', 'super_admin'], true), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeRole($request);
        if ($request->filled('code')) {
            $code = basename(parse_url((string) $request->query('code'), PHP_URL_PATH) ?: '');
            abort_unless(preg_match('/^[A-Za-z0-9]{48}$/', $code), 422);
            return redirect()->route('scan.show', $code);
        }
        return view('scan', ['order' => null, 'pass' => null]);
    }

    public function show(Request $request, string $code)
    {
        $this->authorizeRole($request);
        $hash = hash('sha256', $code);
        $pass = AdmissionPass::with('order.items')->where('token_hash', $hash)->first();
        $order = $pass?->order ?: Order::with('items')->where('ticket_code_hash', $hash)->first();
        if ($order && $request->user()->role === 'shop_admin') abort_unless($order->kind === 'souvenir', 403);
        return view('scan', compact('order', 'pass', 'code'));
    }

    public function redeem(Request $request, string $code, OrderService $service)
    {
        $this->authorizeRole($request);
        $hash = hash('sha256', $code);
        $pass = AdmissionPass::where('token_hash', $hash)->first();
        if ($pass) {
            $service->redeemAdmission($pass, $request->user());
        } else {
            $order = Order::where('ticket_code_hash', $hash)->firstOrFail();
            $service->redeem($order, $request->user());
        }
        return redirect()->route('scan.show', $code)->with('success', 'ยืนยันการใช้สิทธิ์แล้ว');
    }
}
