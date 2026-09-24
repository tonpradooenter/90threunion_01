<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\OrderService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(OrderService::class)->expire())->name('expire-orders')->everyMinute()->withoutOverlapping();

Artisan::command('reunion:bootstrap-admin {email} {proof}', function (string $email, string $proof) {
    DB::transaction(function () use ($email, $proof): void {
        if (User::where('role', 'super_admin')->exists()) {
            throw new RuntimeException('A super administrator already exists. Assign further roles in the admin console.');
        }
        $user = User::where('email', mb_strtolower($email))->lockForUpdate()->firstOrFail();
        $storedProof = Cache::get('admin_proof_'.$user->id);
        if (!$storedProof || !hash_equals($storedProof, hash('sha256', strtoupper($proof)))) {
            throw new RuntimeException('Account proof is invalid or expired. Sign in to that account and generate a fresh code.');
        }
        $user->update(['role' => 'super_admin']);
        Cache::forget('admin_proof_'.$user->id);
        DB::table('activity_logs')->insert(['actor_id' => $user->id, 'action' => 'admin_bootstrap', 'created_at' => now(), 'updated_at' => now()]);
    });
    $this->info('Existing account promoted to super administrator.');
})->purpose('Promote the first registered administrator without creating a default password');
