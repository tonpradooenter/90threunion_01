<?php

namespace App\Services;

use App\Models\BookablePlace;
use App\Models\AdmissionPass;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use App\Models\DiningMenu;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function place(User $customer, User $actor, string $kind, int $resourceId, int $quantity, string $key, ?string $dietaryNotes = null, ?int $diningMenuId = null): Order
    {
        $this->sweepIfDue();
        return DB::transaction(function () use ($customer, $actor, $kind, $resourceId, $quantity, $key, $dietaryNotes, $diningMenuId) {
            User::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $existing = Order::where('client_key', $key)->first();
            if ($existing) {
                abort_unless($existing->user_id === $customer->id && $existing->actor_id === $actor->id, 403);
                return $existing;
            }
            if (Order::where('user_id', $customer->id)->whereIn('status', ['awaiting_slip', 'pending_review', 'changes_requested'])->count() >= 3) {
                throw ValidationException::withMessages(['order' => 'มีรายการที่ยังไม่เสร็จครบ 3 รายการแล้ว กรุณาดำเนินการรายการเดิมก่อน']);
            }

            $menu = null;
            if ($kind === 'souvenir') {
                $product = Product::whereKey($resourceId)->where('kind', $kind)->where('is_active', true)->lockForUpdate()->firstOrFail();
                if ($product->available < $quantity) {
                    throw ValidationException::withMessages(['quantity' => 'สินค้าคงเหลือไม่เพียงพอ']);
                }
                $unitPrice = $product->price_satang;
                $label = $product->name;
                $product->increment('stock_held', $quantity);
            } elseif ($kind === 'table') {
                $quantity = 1;
                $zoneCode = BookablePlace::whereKey($resourceId)->value('zone');
                $zone = Zone::whereKey($zoneCode)->where('is_active', true)->lockForUpdate()->firstOrFail();
                $place = BookablePlace::whereKey($resourceId)->where('kind', $kind)->where('is_active', true)->lockForUpdate()->firstOrFail();
                if ($place->held_by_order_id || $place->sold_by_order_id) {
                    throw ValidationException::withMessages(['place' => 'ที่นั่งหรือโต๊ะนี้ถูกจองแล้ว กรุณาเลือกใหม่']);
                }
                $unitPrice = $zone->price_satang;
                $label = "โต๊ะ {$place->zone} {$place->label} ({$place->capacity} ท่าน รวมอาหารและคอนเสิร์ต)";
                if ($diningMenuId) {
                    $menu = DiningMenu::whereKey($diningMenuId)->where('is_active', true)->lockForUpdate()->first();
                }
                if (!$menu && DiningMenu::where('is_active', true)->exists()) {
                    throw ValidationException::withMessages(['dining_menu_id' => 'กรุณาเลือกชุดอาหารที่เปิดให้จอง']);
                }
                if ($menu) {
                    $label .= ' · '.$menu->name;
                }
            } else {
                abort(422, 'ประเภทการจองไม่ถูกต้อง');
            }

            $order = Order::create([
                'user_id' => $customer->id,
                'actor_id' => $actor->id,
                'kind' => $kind,
                'status' => 'awaiting_slip',
                'client_key' => $key,
                'total_satang' => $unitPrice * $quantity,
                'included_guests' => $kind === 'table' ? $place->capacity : null,
                'expires_at' => now()->addMinutes(30),
                'dietary_notes' => $kind === 'table' ? $dietaryNotes : null,
                'dining_menu_id' => $menu?->id,
                'dining_menu_label' => $menu?->name,
            ]);
            $order->items()->create(['product_id' => $kind === 'souvenir' ? $resourceId : null, 'label' => $label, 'quantity' => $quantity, 'unit_price_satang' => $unitPrice]);
            if ($kind === 'table') {
                $place->update(['held_by_order_id' => $order->id]);
                $order->places()->create(['bookable_place_id' => $place->id]);
            }
            $this->log($actor, $order, 'order_created');
            return $order;
        }, 3);
    }

    public function submitSlip(Order $order, User $actor, string $path): void
    {
        DB::transaction(function () use ($order, $actor, $path) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_unless($order->user_id === $actor->id || (in_array($actor->role, ['support', 'super_admin'], true) && $order->actor_id === $actor->id), 403);
            if (!in_array($order->status, ['awaiting_slip', 'changes_requested'], true) || $order->expires_at->isPast()) {
                throw ValidationException::withMessages(['slip' => 'รายการนี้หมดเวลาหรือส่งหลักฐานไปแล้ว']);
            }
            $order->update(['slip_path' => $path, 'status' => 'pending_review', 'finance_comment' => null]);
            $this->log($actor, $order, 'slip_submitted');
        }, 3);
    }

    public function review(Order $order, User $reviewer, string $decision, ?string $comment = null): void
    {
        abort_unless(in_array($reviewer->role, ['finance', 'super_admin'], true), 403);
        abort_unless(in_array($decision, ['approve', 'changes', 'reject'], true), 422);
        DB::transaction(function () use ($order, $reviewer, $decision, $comment) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if($order->actor_id === $reviewer->id, 403, 'ผู้สร้างรายการไม่สามารถอนุมัติเองได้');
            if ($order->status !== 'pending_review') {
                throw ValidationException::withMessages(['status' => 'รายการนี้ได้รับการตรวจแล้ว']);
            }
            if ($decision === 'approve') {
                if ($order->kind === 'souvenir') {
                    foreach ($order->items as $item) {
                        $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
                        $product->decrement('stock_held', $item->quantity);
                        $product->increment('stock_sold', $item->quantity);
                    }
                } else {
                    foreach ($order->places as $reservation) {
                        $place = BookablePlace::whereKey($reservation->bookable_place_id)->lockForUpdate()->firstOrFail();
                        abort_unless($place->held_by_order_id === $order->id, 409);
                        $place->update(['held_by_order_id' => null, 'sold_by_order_id' => $order->id]);
                    }
                }
                if ($order->kind === 'table') {
                    $capacity = $order->included_guests;
                    for ($guest = 1; $guest <= $capacity; $guest++) {
                        $code = Str::random(48);
                        $order->admissionPasses()->create(['guest_number' => $guest, 'token_hash' => hash('sha256', $code), 'token_encrypted' => Crypt::encryptString($code)]);
                    }
                } else {
                    $code = Str::random(48);
                    $order->ticket_code_hash = hash('sha256', $code);
                    $order->ticket_code_encrypted = Crypt::encryptString($code);
                }
                $order->status = 'approved';
                $order->reviewed_by = $reviewer->id;
                $order->reviewed_at = now();
                $order->save();
            } elseif ($decision === 'changes') {
                $order->update(['status' => 'changes_requested', 'finance_comment' => $comment, 'reviewed_by' => $reviewer->id, 'reviewed_at' => now(), 'expires_at' => now()->addHours(24)]);
            } else {
                $this->releaseHold($order);
                $order->update(['status' => 'rejected', 'finance_comment' => $comment, 'reviewed_by' => $reviewer->id, 'reviewed_at' => now()]);
            }
            $this->log($reviewer, $order, match ($decision) { 'approve' => 'payment_approved', 'changes' => 'payment_changes_requested', 'reject' => 'payment_rejected' });
        }, 3);
    }

    public function redeem(Order $order, User $scanner): void
    {
        abort_unless(in_array($scanner->role, ['scanner', 'shop_admin', 'super_admin'], true), 403);
        DB::transaction(function () use ($order, $scanner) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->kind !== 'souvenir' || $order->status !== 'approved' || $order->redeemed_at) {
                throw ValidationException::withMessages(['ticket' => 'ตั๋วนี้ใช้ไปแล้วหรือยังไม่อนุมัติ']);
            }
            if ($order->kind === 'souvenir') {
                foreach ($order->items as $item) {
                    $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
                    $product->decrement('stock_sold', $item->quantity);
                    $product->decrement('stock_on_hand', $item->quantity);
                }
            }
            $order->update(['status' => 'redeemed', 'redeemed_at' => now(), 'redeemed_by' => $scanner->id]);
            $this->log($scanner, $order, 'ticket_redeemed');
        }, 3);
    }

    public function redeemAdmission(AdmissionPass $pass, User $scanner): void
    {
        abort_unless(in_array($scanner->role, ['scanner', 'super_admin'], true), 403);
        DB::transaction(function () use ($pass, $scanner) {
            $pass = AdmissionPass::whereKey($pass->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($pass->order_id)->lockForUpdate()->firstOrFail();
            if ($pass->redeemed_at || $order->kind !== 'table' || !in_array($order->status, ['approved', 'redeemed'], true)) {
                throw ValidationException::withMessages(['ticket' => 'บัตรผ่านนี้ใช้ไปแล้วหรือยังไม่อนุมัติ']);
            }
            $pass->update(['redeemed_at' => now(), 'redeemed_by' => $scanner->id]);
            if (!$order->admissionPasses()->whereNull('redeemed_at')->exists()) {
                $order->update(['status' => 'redeemed', 'redeemed_at' => now(), 'redeemed_by' => $scanner->id]);
            }
            $this->log($scanner, $order, 'admission_redeemed');
        }, 3);
    }

    public function expire(): int
    {
        $count = 0;
        Order::whereIn('status', ['awaiting_slip', 'changes_requested'])->where('expires_at', '<', now())->orderBy('id')->chunkById(100, function ($orders) use (&$count) {
            foreach ($orders as $order) {
                DB::transaction(function () use ($order, &$count) {
                    $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                    if (!in_array($order->status, ['awaiting_slip', 'changes_requested'], true) || $order->expires_at->isFuture()) return;
                    $this->releaseHold($order);
                    $order->update(['status' => 'expired']);
                    $this->log(null, $order, 'order_expired');
                    $count++;
                }, 3);
            }
        });
        return $count;
    }

    public function sweepIfDue(): void
    {
        if (Cache::add('reunion_expire_sweep', true, now()->addMinute())) {
            $this->expire();
        }
    }

    private function releaseHold(Order $order): void
    {
        if ($order->kind === 'souvenir') {
            foreach ($order->items as $item) {
                Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail()->decrement('stock_held', $item->quantity);
            }
        } else {
            foreach ($order->places as $reservation) {
                BookablePlace::whereKey($reservation->bookable_place_id)->where('held_by_order_id', $order->id)->update(['held_by_order_id' => null]);
            }
        }
    }

    private function log(?User $actor, Order $order, string $action): void
    {
        DB::table('activity_logs')->insert(['actor_id' => $actor?->id, 'order_id' => $order->id, 'action' => $action, 'created_at' => now(), 'updated_at' => now()]);
    }
}
