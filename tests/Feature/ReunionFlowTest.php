<?php

namespace Tests\Feature;

use App\Models\BookablePlace;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use App\Models\DiningMenu;
use App\Services\OrderService;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReunionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_cannot_be_double_booked_and_each_guest_has_one_redeemable_admission(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $finance = User::factory()->create(['role' => 'finance']);
        $scanner = User::factory()->create(['role' => 'scanner']);
        Zone::create(['code' => 'A', 'price_satang' => 500000, 'is_active' => true, 'color' => '#e5a900', 'sort_order' => 1]);
        $table = BookablePlace::create(['kind' => 'table', 'zone' => 'A', 'label' => '01', 'capacity' => 8, 'is_active' => true]);
        $service = app(OrderService::class);
        $order = $service->place($customer, $customer, 'table', $table->id, 1, (string) Str::uuid(), 'แพ้กุ้ง');
        $this->assertSame(500000, $order->total_satang);
        try {
            $service->place($other, $other, 'table', $table->id, 1, (string) Str::uuid());
            $this->fail('The same table was reserved twice.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('orders', 1);
        }
        $service->submitSlip($order, $customer, 'slips/test.png');
        $table->update(['capacity' => 10]); // Later layout edits cannot change a paid order's admission count.
        $service->review($order, $finance, 'approve');
        $this->assertSame(8, $order->admissionPasses()->count());
        $first = $order->admissionPasses()->first();
        $service->redeemAdmission($first, $scanner);
        $this->assertSame('approved', $order->fresh()->status);
        $this->expectException(ValidationException::class);
        $service->redeemAdmission($first, $scanner);
    }

    public function test_expired_souvenir_hold_releases_stock_without_issuing_ticket(): void
    {
        $customer = User::factory()->create();
        $product = Product::create(['kind' => 'souvenir', 'name' => 'Demo', 'price_satang' => 10000, 'stock_on_hand' => 1, 'is_active' => true]);
        $service = app(OrderService::class);
        $order = $service->place($customer, $customer, 'souvenir', $product->id, 1, (string) Str::uuid());
        $this->assertSame(0, $product->fresh()->available);
        $this->travel(31)->minutes();
        $this->assertSame(1, $service->expire());
        $this->assertSame('expired', $order->fresh()->status);
        $this->assertSame(1, $product->fresh()->available);
        $this->assertNull($order->fresh()->ticket_code_hash);
    }

    public function test_rejected_slip_releases_table_for_another_customer(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $finance = User::factory()->create(['role' => 'finance']);
        Zone::create(['code' => 'A', 'price_satang' => 500000, 'is_active' => true, 'color' => '#e5a900', 'sort_order' => 1]);
        $table = BookablePlace::create(['kind' => 'table', 'zone' => 'A', 'label' => '01', 'capacity' => 8, 'is_active' => true]);
        $service = app(OrderService::class);
        $order = $service->place($customer, $customer, 'table', $table->id, 1, (string) Str::uuid());
        $service->submitSlip($order, $customer, 'slips/rejected.png');
        $service->review($order, $finance, 'reject', 'ยอดเงินไม่ตรง');
        $this->assertNull($table->fresh()->held_by_order_id);
        $this->assertSame('rejected', $order->fresh()->status);
        $this->assertNotNull($service->place($other, $other, 'table', $table->id, 1, (string) Str::uuid())->id);
    }

    public function test_table_shop_shows_600_seeded_tables_in_ten_zones(): void
    {
        $this->seed(\Database\Seeders\DemoSeeder::class);
        $this->assertDatabaseCount('bookable_places', 600);
        $this->assertDatabaseCount('zones', 10);
        $this->get('/shop/table?zone=J')->assertOk()->assertSee('J60');
    }

    public function test_customer_finance_and_scanner_complete_the_web_flow(): void
    {
        Storage::fake('private');
        $customer = User::factory()->create();
        $finance = User::factory()->create(['role' => 'finance']);
        $scanner = User::factory()->create(['role' => 'scanner']);
        Zone::create(['code' => 'A', 'price_satang' => 500000, 'is_active' => true, 'color' => '#e5a900', 'sort_order' => 1]);
        $table = BookablePlace::create(['kind' => 'table', 'zone' => 'A', 'label' => '01', 'capacity' => 8, 'is_active' => true]);
        $this->actingAs($customer)->post('/orders', ['kind' => 'table', 'resource_id' => $table->id, 'quantity' => 1, 'client_key' => (string) Str::uuid()])->assertRedirect();
        $order = \App\Models\Order::firstOrFail();
        $this->actingAs($customer)->post(route('orders.slip', $order), ['slip' => UploadedFile::fake()->image('slip.png')])->assertRedirect();
        $this->assertSame('pending_review', $order->fresh()->status);
        $this->actingAs($finance)->get(route('finance.index'))->assertOk();
        $this->actingAs($finance)->post(route('finance.review', $order), ['decision' => 'approve'])->assertRedirect();
        $this->actingAs($customer)->get(route('orders.show', $order))->assertOk()->assertSee('บัตรผ่านท่านที่ 1');
        $code = Crypt::decryptString($order->admissionPasses()->first()->token_encrypted);
        $this->actingAs($scanner)->get(route('scan.show', $code))->assertOk();
        $this->actingAs($scanner)->post(route('scan.redeem', $code))->assertRedirect();
        $this->assertNotNull($order->admissionPasses()->first()->redeemed_at);
    }

    public function test_staff_roles_cannot_bypass_their_admin_section(): void
    {
        $customer = User::factory()->create();
        $shop = User::factory()->create(['role' => 'shop_admin']);
        $tableAdmin = User::factory()->create(['role' => 'table_admin']);
        $this->actingAs($customer)->get('/admin/products')->assertForbidden();
        $this->actingAs($shop)->get('/admin/products')->assertOk();
        $this->actingAs($shop)->get('/admin/users')->assertForbidden();
        $this->actingAs($tableAdmin)->get('/admin/dining-menus')->assertOk();
        $this->actingAs($shop)->get('/admin/dining-menus')->assertForbidden();
    }

    public function test_first_admin_requires_proof_from_the_authenticated_account(): void
    {
        $user = User::factory()->create(['email' => 'director@example.test']);
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has('admin_proof_'.$user->id));
        $this->assertSame('customer', $user->fresh()->role);

        $response = $this->actingAs($user)->post(route('account.adminProof'));
        $response->assertRedirect(route('account'));
        $proof = session('admin_proof');
        $this->assertNotEmpty($proof);
        $this->artisan('reunion:bootstrap-admin', ['email' => $user->email, 'proof' => $proof])
            ->assertSuccessful();
        $this->assertSame('super_admin', $user->fresh()->role);
    }

    public function test_product_image_is_served_from_private_storage_only_for_active_product(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('products/demo.png', 'image-bytes');
        $product = Product::create(['kind' => 'souvenir', 'name' => 'Demo', 'price_satang' => 10000, 'stock_on_hand' => 1, 'is_active' => true, 'image_path' => 'products/demo.png']);
        $this->get(route('products.image', $product))->assertOk();
        $product->update(['is_active' => false]);
        $this->get(route('products.image', $product))->assertNotFound();
    }

    public function test_support_can_find_social_only_customer_by_id(): void
    {
        $customer = User::factory()->create(['email' => null, 'phone' => null, 'password' => null]);
        $support = User::factory()->create(['role' => 'support']);
        $this->actingAs($support)->get(route('assist.index', ['identifier' => '#'.$customer->id]))
            ->assertOk()->assertSee($customer->name);
    }

    public function test_table_order_requires_active_menu_and_preserves_the_chosen_name(): void
    {
        $customer = User::factory()->create();
        Zone::create(['code' => 'A', 'price_satang' => 500000, 'is_active' => true, 'color' => '#e5a900', 'sort_order' => 1]);
        $table = BookablePlace::create(['kind' => 'table', 'zone' => 'A', 'label' => '01', 'capacity' => 8, 'is_active' => true]);
        $menu = DiningMenu::create(['name' => 'ชุดอาหารทดสอบ', 'is_active' => true, 'sort_order' => 1]);
        $service = app(OrderService::class);
        try {
            $service->place($customer, $customer, 'table', $table->id, 1, (string) Str::uuid());
            $this->fail('A table order without a menu was accepted.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('orders', 0);
        }
        $order = $service->place($customer, $customer, 'table', $table->id, 1, (string) Str::uuid(), 'แพ้กุ้ง', $menu->id);
        $menu->update(['name' => 'ชื่อใหม่']);
        $this->assertSame('ชุดอาหารทดสอบ', $order->fresh()->dining_menu_label);
        $this->assertSame('แพ้กุ้ง', $order->fresh()->dietary_notes);
    }
}
