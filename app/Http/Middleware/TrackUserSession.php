<?php
namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSession
{
    public function handle(Request $request,Closure $next): Response
    {
        $user=$request->user();if(!$user)return $next($request);
        try{
            if(Schema::hasTable('user_sessions')){
                $sessionId=$request->session()->getId();$record=UserSession::where('session_id',$sessionId)->first();
                $idleExpired = $record && $record->last_seen_at && $record->last_seen_at->lt(now()->subMinutes((int) config('security.session_idle_minutes', 120)));
                if($record&&($record->user_id!==$user->id||$record->revoked_at||$idleExpired)){$record->update(['revoked_at'=>now()]);auth()->logout();$request->session()->invalidate();$request->session()->regenerateToken();abort(401,'انتهت الجلسة أو تم إنهاؤها. سجّل الدخول مرة أخرى.');}
                if(!$record)$record=UserSession::create(['user_id'=>$user->id,'session_id'=>$sessionId,'device_label'=>mb_substr((string)$request->userAgent(),0,150),'ip_address'=>$request->ip(),'user_agent'=>mb_substr((string)$request->userAgent(),0,2000),'last_seen_at'=>now()]);
                elseif(!$record->last_seen_at||$record->last_seen_at->lt(now()->subMinute()))$record->update(['last_seen_at'=>now(),'ip_address'=>$request->ip()]);
            }
        }catch(\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception){throw $exception;}catch(\Throwable){/* Keep access available if session registry storage is temporarily unavailable. */}
        return $next($request);
    }
}
