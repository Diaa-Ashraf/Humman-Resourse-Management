<?php

namespace Database\Seeders;

use App\Models\DataRetentionPolicy;
use Illuminate\Database\Seeder;

class GovernancePolicySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'audit_logs' => ['سجلات التدقيق', 365, 'الامتثال لمتطلبات التدقيق الأمني ونظام الرقابة الداخلية.'],
            'user_sessions' => ['سجلات الجلسات', 90, 'إدارة جلسات العمل وحماية أمن الحسابات.'],
            'employee_documents' => ['مستندات الموظفين', 1825, 'الامتثال لقانون العمل بحفظ ملفات ومستندات العاملين مدة 5 سنوات.'],
            'employee_change_logs' => ['سجل تغييرات الموظفين', 1825, 'التدقيق الإداري وتتبع السجل الوظيفي للموظفين.'],
        ];

        foreach ($defaults as $category => [$label, $days, $legal]) {
            DataRetentionPolicy::updateOrCreate(
                ['data_category' => $category],
                [
                    'label' => $label,
                    'retention_days' => $days,
                    'cleanup_enabled' => in_array($category, ['audit_logs', 'user_sessions']),
                    'legal_basis' => $legal,
                ]
            );
        }
    }
}
