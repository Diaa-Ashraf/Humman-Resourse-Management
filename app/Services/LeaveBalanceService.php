<?php
namespace App\Services;

use App\Models\{CompanyHoliday,Employee,Leave,LeaveBalance,LeaveBalanceTransaction,LeaveType};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveBalanceService
{
    public function countDays(LeaveType $type,Employee $employee,Carbon $start,Carbon $end):float
    {
        $holidayRows=CompanyHoliday::where(fn($q)=>$q->whereBetween('holiday_date',[$start->toDateString(),$end->toDateString()])->orWhere('is_recurring',true))->where(fn($q)=>$q->whereNull('branch_id')->orWhere('branch_id',$employee->branch_id))->get(['holiday_date','is_recurring']);
        $holidays=$holidayRows->map(fn($holiday)=>$holiday->is_recurring?$holiday->holiday_date->format('m-d'):$holiday->holiday_date->toDateString())->all();
        $days=0;for($day=$start->copy()->startOfDay();$day->lte($end);$day->addDay()){
            if($type->exclude_weekends && in_array($day->dayOfWeek,[Carbon::FRIDAY,Carbon::SATURDAY],true))continue;
            if(in_array($day->toDateString(),$holidays,true)||in_array($day->format('m-d'),$holidays,true))continue;$days++;
        }return (float)$days;
    }
    public function balanceFor(Employee $employee,LeaveType $type,int $year):LeaveBalance
    {
        $annual=(float)($type->annual_entitlement_days??$type->max_days??0);
        $previous=LeaveBalance::where('employee_id',$employee->id)->where('leave_type_id',$type->id)->where('year',$year-1)->first();
        $carry=max(0,(float)($previous?->available??0));if($type->carryover_cap_days!==null)$carry=min($carry,(float)$type->carryover_cap_days);
        $balance=LeaveBalance::firstOrCreate(['employee_id'=>$employee->id,'leave_type_id'=>$type->id,'year'=>$year],['opening_balance'=>0,'carried_days'=>$carry,'accrued_days'=>0,'used_days'=>0]);
        $employmentStart=max($employee->hire_date?->copy()??Carbon::create($year,1,1),Carbon::create($year,1,1));
        $accrual=0;
        if($annual>0 && $employmentStart->year===$year && $employmentStart->lte(today())){
            $months=12-$employmentStart->month+1;
            $accrual=round(($annual/12)*$months,2);
            if($employmentStart->day>1)$accrual=round($accrual-($annual/12)*(($employmentStart->day-1)/$employmentStart->daysInMonth),2);
        }
        if($balance->accrued_days!=$accrual){$delta=round($accrual-(float)$balance->accrued_days,2);$balance->update(['accrued_days'=>$accrual]);if($delta>0)LeaveBalanceTransaction::create(['leave_balance_id'=>$balance->id,'type'=>'accrual','days'=>$delta,'notes'=>'استحقاق متدرج لسنة '.$year,'created_by'=>auth()->id()]);}
        return $balance->refresh();
    }
    public function assertAvailable(Employee $employee,LeaveType $type,Carbon $start,Carbon $end,?int $excludeLeaveId=null):array
    {
        $segments=[];$cursor=$start->copy()->startOfDay();
        while($cursor->lte($end)){
            $yearEnd=$cursor->copy()->endOfYear();$segmentEnd=$yearEnd->lt($end)?$yearEnd:$end;
            $days=$this->countDays($type,$employee,$cursor,$segmentEnd);
            if($days>0){$balance=$this->balanceFor($employee,$type,$cursor->year);
                $pendingRequests=Leave::where('employee_id',$employee->id)->where('leave_type_id',$type->id)->where('status','pending')
                    ->whereDate('start_date','<=',$segmentEnd->toDateString())->whereDate('end_date','>=',$cursor->toDateString())
                    ->when($excludeLeaveId,fn($q)=>$q->where('id','!=',$excludeLeaveId))->get();
                $pending=$pendingRequests->sum(function($leave)use($type,$employee,$cursor,$segmentEnd){
                    $requestStart=Carbon::parse($leave->start_date)->max($cursor);
                    $requestEnd=Carbon::parse($leave->end_date)->min($segmentEnd);
                    return $this->countDays($type,$employee,$requestStart,$requestEnd);
                });
                if((float)$balance->available-(float)$pending+0.001<$days)throw \Illuminate\Validation\ValidationException::withMessages(['leave_type_id'=>"الرصيد غير كافٍ لسنة {$cursor->year}. المتاح بعد الطلبات المعلقة: ".number_format(max(0,$balance->available-$pending),2)." يوم."]);
                $segments[$cursor->year]=['balance'=>$balance,'days'=>$days];
            }
            $cursor=$segmentEnd->copy()->addDay();
        }return $segments;
    }
    public function applyApproved(Leave $leave,array $segments):void
    {
        foreach($segments as $year=>$segment){$balance=LeaveBalance::whereKey($segment['balance']->id)->lockForUpdate()->first();$balance->increment('used_days',$segment['days']);LeaveBalanceTransaction::create(['leave_balance_id'=>$balance->id,'leave_id'=>$leave->id,'type'=>'leave_used','days'=>-$segment['days'],'notes'=>'اعتماد طلب إجازة #'.$leave->id,'created_by'=>auth()->id()]);}
    }
}
