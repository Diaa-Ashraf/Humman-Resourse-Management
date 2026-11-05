<?php
namespace App\Services;

use App\Models\{AdvanceInstallment, Employee, PayrollPolicy, PayrollTaxBracket};
use Carbon\Carbon;

class PayrollCalculator
{
    private array $policies=[];
    private $taxBrackets;
    public function __construct(private Carbon $start, private Carbon $end)
    {
        $this->policies=PayrollPolicy::whereDate('effective_from','<=',$end)->where(fn($q)=>$q->whereNull('effective_to')->orWhereDate('effective_to','>=',$end))->orderBy('effective_from')->get()->groupBy('key')->map(fn($rows)=>(float)$rows->last()->value)->all();
        $this->taxBrackets=PayrollTaxBracket::whereDate('effective_from','<=',$end)->where(fn($q)=>$q->whereNull('effective_to')->orWhereDate('effective_to','>=',$end))->orderBy('sort_order')->get();
    }
    private function p(string $key,float $default=0):float{return $this->policies[$key]??$default;}

    public function calculate(Employee $employee): array
    {
        $base=(float)$employee->base_salary;
        $earnings=[];$deductions=[];$taxable=$base;$insurableWage=$base;
        $componentAssignments=$employee->relationLoaded('salaryComponents')?$employee->salaryComponents:$employee->salaryComponents()->with('component')->whereDate('effective_from','<=',$this->end)->where(fn($q)=>$q->whereNull('effective_to')->orWhereDate('effective_to','>=',$this->start))->get();
        foreach($componentAssignments as $assignment){
            if($assignment->effective_from && $assignment->effective_from->gt($this->end))continue;
            if($assignment->effective_to && $assignment->effective_to->lt($this->start))continue;
            $component=$assignment->component;if(!$component||!$component->is_active)continue;
            $amount=(float)$assignment->amount;
            if($component->type==='earning'){$earnings[]=['name'=>$component->name,'amount'=>$amount];if($component->taxable)$taxable+=$amount;if($component->insurable)$insurableWage+=$amount;}
            else $deductions[]=['name'=>$component->name,'amount'=>$amount];
        }
        $bonuses=$employee->relationLoaded('bonuses')?$employee->bonuses:$employee->bonuses()->where('status','approved')->whereBetween('bonus_date',[$this->start->toDateString(),$this->end->toDateString()])->get();
        foreach($bonuses as $bonus){$earnings[]=['name'=>'مكافأة: '.$bonus->reason,'amount'=>(float)$bonus->amount];$taxable+=(float)$bonus->amount;}
        $approvedDeductions=$employee->relationLoaded('deductions')?$employee->deductions:$employee->deductions()->where('status','approved')->whereBetween('deduction_date',[$this->start->toDateString(),$this->end->toDateString()])->get();
        foreach($approvedDeductions as $item)$deductions[]=['name'=>'خصم: '.$item->reason,'amount'=>(float)$item->amount];

        $attendance=$employee->relationLoaded('attendances')?$employee->attendances:$employee->attendances()->with('shift')->where('review_status','approved')->whereBetween('attendance_date',[$this->start->toDateString(),$this->end->toDateString()])->get();
        $absentDays=$attendance->where('status','absent')->count();
        $lateMinutes=$attendance->sum('late_minutes');
        $overtimeAmount=0;
        $standardDays=max(1,$this->p('standard_work_days',26));$standardHours=max(1,$this->p('standard_work_hours',8));
        foreach($attendance as $entry){
            $hours=$entry->overtime_minutes/60;
            $multiplier=(float)($entry->shift?->overtime_multiplier??1.5);
            $overtimeAmount += ($base/($standardDays*$standardHours))*$hours*$multiplier;
        }
        $absentAmount=$absentDays*($base/$standardDays);
        $lateAmount=$lateMinutes*$this->p('late_deduction_per_minute',0);
        if($overtimeAmount>0)$earnings[]=['name'=>'عمل إضافي','amount'=>round($overtimeAmount,2)];
        if($absentAmount>0)$deductions[]=['name'=>'غياب ('.$absentDays.' يوم)','amount'=>round($absentAmount,2)];
        if($lateAmount>0)$deductions[]=['name'=>'تأخير ('.$lateMinutes.' دقيقة)','amount'=>round($lateAmount,2)];

        $gross=$base+array_sum(array_column($earnings,'amount'));
        $insuranceWage=$employee->insurance_wage!==null?(float)$employee->insurance_wage:$insurableWage;
        $insuranceWage=min($this->p('insurance_wage_ceiling',16700),max($this->p('insurance_wage_floor',2700),$insuranceWage));
        $insuranceAmount=$employee->insurance_enabled?round($insuranceWage*$this->p('insurance_employee_rate',0)/100,2):0;
        $employerInsuranceAmount=$employee->insurance_enabled?round($insuranceWage*$this->p('insurance_employer_rate',0)/100,2):0;
        if($insuranceAmount>0)$deductions[]=['name'=>'حصة الموظف في التأمينات','amount'=>$insuranceAmount];

        $taxAmount=0;
        if($employee->tax_enabled){
            $annualTaxable=max(0,($taxable+$overtimeAmount)*12-$this->p('tax_personal_allowance',0)-$insuranceAmount*12);
            $annualTax=0;
            foreach($this->taxBrackets as $bracket){$lower=(float)$bracket->lower_bound;$upper=$bracket->upper_bound===null?INF:(float)$bracket->upper_bound;$taxableBand=max(0,min($annualTaxable,$upper)-$lower);$annualTax+=$taxableBand*(float)$bracket->rate_percent/100;}
            $taxAmount=round($annualTax/12,2);
            if($taxAmount>0)$deductions[]=['name'=>'ضريبة المرتبات المقدرة','amount'=>$taxAmount];
        }

        $installments = $employee->relationLoaded('advances')
            ? $employee->advances->flatMap(fn($advance) => $advance->installmentRecords ?? $advance->installments)
                ->filter(fn($item) => is_object($item) && $item->status === 'scheduled' && $item->due_date && $item->due_date->between($this->start, $this->end))
                ->values()
            : AdvanceInstallment::with('advance')
                ->whereHas('advance', fn($q) => $q->where('employee_id', $employee->id)->where('status', 'approved'))
                ->where('status', 'scheduled')
                ->whereBetween('due_date', [$this->start->toDateString(), $this->end->toDateString()])
                ->get();
        $advanceAmount=(float)$installments->sum('amount');
        if($advanceAmount>0)$deductions[]=['name'=>'قسط سلفة/قرض','amount'=>$advanceAmount];
        $deductionTotal=array_sum(array_column($deductions,'amount'));
        $net=max(0,round($gross-$deductionTotal,2));
        return [
            'amount'=>round($base,2),'gross_amount'=>round($gross,2),'overtime_amount'=>round($overtimeAmount,2),'absence_amount'=>round($absentAmount,2),'late_amount'=>round($lateAmount,2),
            'insurance_amount'=>$insuranceAmount,'employer_insurance_amount'=>$employerInsuranceAmount,'tax_amount'=>$taxAmount,'advance_amount'=>$advanceAmount,'total'=>$net,
            'earnings_breakdown'=>$earnings,'deductions_breakdown'=>$deductions,
            'calculation_snapshot'=>['period_start'=>$this->start->toDateString(),'period_end'=>$this->end->toDateString(),'employee_id'=>$employee->id,'attendance_days'=>$attendance->count(),'absent_days'=>$absentDays,'late_minutes'=>$lateMinutes,'overtime_minutes'=>$attendance->sum('overtime_minutes'),'insurance_wage'=>$insuranceWage,'employer_insurance_amount'=>$employerInsuranceAmount,'advance_installment_ids'=>$installments->modelKeys(),'policy_values'=>$this->policies,'tax_brackets'=>$this->taxBrackets->map(fn($b)=>$b->only(['lower_bound','upper_bound','rate_percent']))->all()]
        ];
    }
}
