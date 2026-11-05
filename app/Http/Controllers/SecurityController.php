<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use App\Services\Totp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SecurityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $sessions = $user->securitySessions()->latest('last_seen_at')->get();
        return view('dashboard.security.index', compact('user', 'sessions'));
    }

    public function beginMfa(Request $request, Totp $totp)
    {
        $request->validate(['current_password' => ['required', 'current_password']]);
        $secret = $totp->secret();
        $request->session()->put('mfa_setup_secret', $secret);
        return view('dashboard.security.setup-mfa', ['secret' => $secret, 'uri' => $totp->uri($secret, $request->user()->email)]);
    }

    public function enableMfa(Request $request, Totp $totp)
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $secret = $request->session()->get('mfa_setup_secret');
        if (!$secret || !$totp->verify($secret, $request->code)) {
            throw ValidationException::withMessages(['code' => 'رمز التحقق غير صحيح. أعد المحاولة أو ابدأ الإعداد من جديد.']);
        }
        $request->user()->forceFill(['mfa_secret' => $secret, 'mfa_enabled_at' => now()])->save();
        $request->session()->forget('mfa_setup_secret');
        return redirect()->route('security.index')->with('success', 'تم تفعيل المصادقة الثنائية.');
    }

    public function disableMfa(Request $request, Totp $totp)
    {
        $request->validate(['current_password' => ['required', 'current_password'], 'code' => ['required', 'digits:6']]);
        if (!$request->user()->mfa_secret || !$totp->verify($request->user()->mfa_secret, $request->code)) {
            throw ValidationException::withMessages(['code' => 'رمز التحقق غير صحيح.']);
        }
        $request->user()->forceFill(['mfa_secret' => null, 'mfa_enabled_at' => null])->save();
        return back()->with('success', 'تم إيقاف المصادقة الثنائية.');
    }

    public function revokeSession(Request $request, UserSession $session)
    {
        abort_unless($session->user_id === $request->user()->id, 404);
        $session->update(['revoked_at' => now()]);
        if ($session->session_id !== $request->session()->getId()) {
            $sessionsTable = config('session.table', 'sessions');
            if (\Illuminate\Support\Facades\Schema::hasTable($sessionsTable)) {
                \Illuminate\Support\Facades\DB::table($sessionsTable)->where('id', $session->session_id)->delete();
            }
        }
        return back()->with('success', 'تم إنهاء الجلسة المحددة.');
    }
}
