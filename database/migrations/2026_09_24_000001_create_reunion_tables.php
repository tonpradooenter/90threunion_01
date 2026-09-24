<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 20);
            $table->string('provider_id');
            $table->timestamps();
            $table->unique(['provider', 'provider_id']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20);
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('price_satang');
            $table->unsignedInteger('stock_on_hand')->default(0);
            $table->unsignedInteger('stock_held')->default(0);
            $table->unsignedInteger('stock_sold')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->index(['kind', 'is_active']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('actor_id')->constrained('users');
            $table->string('kind', 20);
            $table->string('status', 24)->default('awaiting_slip');
            $table->uuid('client_key')->unique();
            $table->unsignedInteger('total_satang');
            $table->unsignedSmallInteger('included_guests')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('dietary_notes')->nullable();
            $table->string('slip_path')->nullable();
            $table->text('finance_comment')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->string('ticket_code_hash', 64)->nullable()->unique();
            $table->text('ticket_code_encrypted')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->foreignId('redeemed_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->index(['status', 'expires_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained();
            $table->string('label');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price_satang');
            $table->timestamps();
        });

        Schema::create('zones', function (Blueprint $table) {
            $table->string('code', 1)->primary();
            $table->unsignedInteger('price_satang');
            $table->boolean('is_active')->default(false);
            $table->string('color', 7);
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();
        });

        Schema::create('bookable_places', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20);
            $table->string('zone', 1);
            $table->string('label', 40);
            $table->unsignedSmallInteger('capacity')->default(8);
            $table->boolean('is_active')->default(true);
            $table->foreignId('held_by_order_id')->nullable()->constrained('orders');
            $table->foreignId('sold_by_order_id')->nullable()->constrained('orders');
            $table->timestamps();
            $table->foreign('zone')->references('code')->on('zones');
            $table->unique(['kind', 'zone', 'label']);
        });

        Schema::create('order_places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bookable_place_id')->constrained();
            $table->unique(['order_id', 'bookable_place_id']);
        });

        Schema::create('admission_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('guest_number');
            $table->string('token_hash', 64)->unique();
            $table->text('token_encrypted');
            $table->timestamp('redeemed_at')->nullable();
            $table->foreignId('redeemed_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->unique(['order_id', 'guest_number']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->foreignId('order_id')->nullable()->constrained();
            $table->string('action', 50);
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['activity_logs', 'admission_passes', 'order_places', 'bookable_places', 'zones', 'order_items', 'orders', 'products', 'user_identities'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
