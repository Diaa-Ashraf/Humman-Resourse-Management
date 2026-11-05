<?php
namespace Database\Seeders;
use App\Models\PayrollPolicy;
use Illuminate\Database\Seeder;
class PayrollPolicySeeder extends Seeder
{
    public function run(): void
    {
        $effective=now()->startOfYear()->toDateString();
        foreach ([
            'insurance_wage_floor'=>['الحد الأدنى لأجر الاشتراك التأميني','2700','جنيه'],
            'insurance_wage_ceiling'=>['الحد الأقصى لأجر الاشتراك التأميني','16700','جنيه'],
            'insurance_employee_rate'=>['نسبة اشتراك الموظف','0','نسبة مئوية — اضبطها حسب الفئة'],
            'insurance_employer_rate'=>['نسبة اشتراك جهة العمل','0','نسبة مئوية — اضبطها حسب الفئة'],
            'tax_personal_allowance'=>['الإعفاء الشخصي السنوي','20000','جنيه'],
            'standard_work_days'=>['أيام العمل القياسية بالشهر','26','يوم'],
            'standard_work_hours'=>['ساعات العمل القياسية باليوم','8','ساعة'],
            'late_deduction_per_minute'=>['خصم الدقيقة المتأخرة','0','جنيه'],
        ] as $key=>[$label,$value,$unit]){
            if (!PayrollPolicy::where('key', $key)->exists()) {
                PayrollPolicy::create(['key'=>$key,'label'=>$label,'value'=>$value,'unit'=>$unit,'effective_from'=>$effective]);
            }
        }
    }
}
