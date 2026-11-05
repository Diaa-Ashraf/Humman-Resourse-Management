<?php
namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class AuditRequests
{
    public function handle(Request $request,Closure $next): Response
    {
        $responseCode=500;
        try{$response=$next($request);$responseCode=$response->getStatusCode();return $response;}
        catch(Throwable $exception){$responseCode=$exception instanceof ValidationException?$exception->status:($exception instanceof AuthorizationException?403:($exception instanceof ModelNotFoundException?404:(method_exists($exception,'getStatusCode')?$exception->getStatusCode():500)));throw $exception;}
        finally{
            try{
                $method=strtoupper($request->method());$route=$request->route();$routeName=$route?->getName();
                $sensitive=$request->is('login')||($request->is('dashboard/*')&&preg_match('/(employees|salaries|payroll|employee-documents|reports|users|profile|audit|retention|internal-comms|employee-requests|branches|sections|attendance|leaves|advances|bonuses|deductions|training|performance|recruitment|shift-assignments)/',$request->path()));
                $denied=$responseCode>=400&&$request->is('dashboard/*');
                if(($method!=='GET'&&$method!=='HEAD')||$sensitive||$denied){
                    if(Schema::hasTable('audit_logs')){
                        $params=[];$subjectType=null;$subjectId=null;foreach(($route?->parameters()??[]) as $key=>$value){if(is_scalar($value))$params[$key]=(string)$value;elseif(is_object($value)&&method_exists($value,'getKey')){$params[$key]=(string)$value->getKey();$subjectType=class_basename($value);$subjectId=(string)$value->getKey();}}
                        AuditLog::create(['user_id'=>auth()->id(),'action'=>$routeName??($method.' '.$request->path()),'route_name'=>$routeName,'method'=>$method,'subject_type'=>$subjectType,'subject_id'=>$subjectId,'status_code'=>$responseCode,'ip_address'=>$request->ip(),'user_agent'=>mb_substr((string)$request->userAgent(),0,2000),'metadata'=>['route_parameters'=>$params,'query_keys'=>array_keys($request->query()),'attempt'=>'login'===$request->path()?'authentication':'request'],'created_at'=>now()]);
                    }
                }
            }catch(Throwable){/* Auditing must never break an authorized business request. */}
        }
    }
}
