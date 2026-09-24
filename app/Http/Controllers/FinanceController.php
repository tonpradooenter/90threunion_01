<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    private function authorizeRole(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['finance', 'super_admin'], true), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeRole($request);
        return view('finance', ['orders' => Order::with('user', 'items')->where('status', 'pending_review')->oldest()->paginate(30)]);
    }

    public function review(Request $request, Order $order, OrderService $service)
    {
        $this->authorizeRole($request);
        $data = $request->validate(['decision' => 'required|in:approve,changes,reject', 'comment' => 'required_if:decision,changes,reject|nullable|string|max:1000']);
        $service->review($order, $request->user(), $data['decision'], $data['comment'] ?? null);
        return redirect()->route('finance.index')->with('success', 'บันทึกผลตรวจสอบแล้ว');
    }
}
