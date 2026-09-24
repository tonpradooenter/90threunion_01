<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function loginForm() { return view('auth.login'); }
    public function registerForm() { return view('auth.register'); }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'identifier' => 'required|string|max:254',
            'password' => ['required', 'confirmed', Password::min(10)],
        ]);
        $identifier = trim(mb_strtolower($data['identifier']));
        $email = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? $identifier : null;
        $phone = $email ? null : preg_replace('/\D+/', '', $identifier);
        if (!$email && !preg_match('/^0[0-9]{9}$/', $phone)) {
            return back()->withErrors(['identifier' => 'กรุณากรอกอีเมลหรือเบอร์โทรศัพท์ 10 หลัก'])->withInput();
        }
        if (User::where($email ? 'email' : 'phone', $email ?: $phone)->exists()) {
            return back()->withErrors(['identifier' => 'บัญชีนี้มีอยู่แล้ว กรุณาเข้าสู่ระบบ'])->withInput();
        }
        $user = User::create(['name' => $data['name'], 'email' => $email, 'phone' => $phone, 'password' => $data['password'], 'role' => 'customer']);
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('account');
    }

    public function login(Request $request)
    {
        $data = $request->validate(['identifier' => 'required|string', 'password' => 'required|string']);
        $identifier = trim(mb_strtolower($data['identifier']));
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        if (Auth::attempt([$field => $identifier, 'password' => $data['password']])) {
            $request->session()->regenerate();
            return redirect()->intended(route('account'));
        }
        return back()->withErrors(['identifier' => 'อีเมล/เบอร์โทรหรือรหัสผ่านไม่ถูกต้อง'])->onlyInput('identifier');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function redirect(Request $request, string $provider)
    {
        abort_unless(in_array($provider, ['google', 'line'], true), 404);
        abort_unless(config("services.{$provider}.client_id") && config("services.{$provider}.client_secret"), 503, 'ช่องทางนี้ยังไม่เปิดใช้งาน');
        $request->session()->put('social_link_user_id', Auth::id());
        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        abort_unless(in_array($provider, ['google', 'line'], true), 404);
        $social = Socialite::driver($provider)->user(); // State and session cookie are checked by Socialite.
        $linkUserId = $request->session()->pull('social_link_user_id');
        $identity = DB::table('user_identities')->where('provider', $provider)->where('provider_id', (string) $social->getId())->first();
        if ($identity) {
            $user = User::findOrFail($identity->user_id);
            if ($linkUserId && (int) $linkUserId !== $user->id) {
                return redirect()->route('account')->withErrors(['social' => 'บัญชีนี้เชื่อมกับสมาชิกอื่นแล้ว']);
            }
        } elseif ($linkUserId) {
            abort_unless(Auth::id() === (int) $linkUserId, 403);
            $user = Auth::user();
            DB::table('user_identities')->insert(['user_id' => $user->id, 'provider' => $provider, 'provider_id' => (string) $social->getId(), 'created_at' => now(), 'updated_at' => now()]);
        } else {
            $email = $social->getEmail() ? mb_strtolower($social->getEmail()) : null;
            $verified = $provider === 'google' && filter_var(data_get($social->user, 'email_verified'), FILTER_VALIDATE_BOOLEAN);
            $user = $verified && $email ? User::where('email', $email)->whereNotNull('email_verified_at')->first() : null;
            if (!$user && $email && User::where('email', $email)->exists()) {
                return redirect()->route('login')->withErrors(['social' => 'อีเมลนี้มีบัญชีแล้ว กรุณาเข้าสู่ระบบเพื่อเชื่อมบัญชี']);
            }
            $user ??= User::create(['name' => $social->getName() ?: 'สมาชิก', 'email' => $verified ? $email : null, 'email_verified_at' => $verified ? now() : null, 'role' => 'customer']);
            DB::table('user_identities')->insert(['user_id' => $user->id, 'provider' => $provider, 'provider_id' => (string) $social->getId(), 'created_at' => now(), 'updated_at' => now()]);
        }
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('account');
    }
}
