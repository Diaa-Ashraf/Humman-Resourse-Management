<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\{AuditLog,User,UserSession};
use App\Services\Totp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class MfaChallengeController extends Controller
{
    public function create(Request $request)
    {
        abort_unless($request->session()->has('mfa_pending_user_id'),404);
        abort_if($request->session()->get('mfa_pending_at',0)<now()->subMinutes(config('security.mfa_challenge_minutes',5))->timestamp,419);
        return view('dashboard.auth.mfa-challenge');
    }

    public function store(Request $request,Totp $totp)
    {
        $request->validate(['code'=>'required|digits:6']);$userId=$request->session()->get('mfa_pending_user_id');$key='mfa:'.$userId.'|'.$request->ip();
        if(RateLimiter::tooManyAttempts($key,5))throw ValidationException::withMessages(['code'=>'تم قفل التحقق مؤقتًا. حاول لاحقًا.']);
        $user=User::find($userId);if(!$user||!$user->mfa_secret||!$totp->verify($user->mfa_secret,$request->code)){RateLimiter::hit($key,300);throw ValidationException::withMessages(['code'=>'رمز التحقق غير صحيح.']);}
        RateLimiter::clear($key);$remember=(bool)$request->session()->get('mfa_pending_remember');$request->session()->forget(['mfa_pending_user_id','mfa_pending_at','mfa_pending_remember']);Auth::login($user,$remember);$request->session()->regenerate();
        UserSession::updateOrCreate(['session_id'=>$request->session()->getId()],['user_id'=>$user->id,'device_label'=>mb_substr((string)$request->userAgent(),0,150),'ip_address'=>$request->ip(),'user_agent'=>mb_substr((string)$request->userAgent(),0,2000),'last_seen_at'=>now(),'revoked_at'=>null]);
        return redirect()->intended(route('dashboard',absolute:false));
    }
}
