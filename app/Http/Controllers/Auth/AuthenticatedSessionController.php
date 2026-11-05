<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSession;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('dashboard.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $user=$request->user();$remember=$request->boolean('remember');$request->session()->regenerate();
        if($user->mfa_enabled_at){Auth::logout();$request->session()->regenerate();$request->session()->put(['mfa_pending_user_id'=>$user->id,'mfa_pending_at'=>now()->timestamp,'mfa_pending_remember'=>$remember]);return redirect()->route('mfa.challenge');}
        UserSession::updateOrCreate(['session_id'=>$request->session()->getId()],['user_id'=>$user->id,'device_label'=>mb_substr((string)$request->userAgent(),0,150),'ip_address'=>$request->ip(),'user_agent'=>mb_substr((string)$request->userAgent(),0,2000),'last_seen_at'=>now(),'revoked_at'=>null]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if($request->user())UserSession::where('session_id',$request->session()->getId())->where('user_id',$request->user()->id)->update(['revoked_at'=>now()]);
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
