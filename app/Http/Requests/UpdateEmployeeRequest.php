<?php

namespace App\Http\Requests;

use App\Services\BranchAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update_employees') ?? false;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');
        $employeeId = $employee instanceof \App\Models\Employee ? $employee->id : $employee;
        $isGlobal = app(BranchAccess::class)->isGlobal($this->user());

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employeeId)],
            'position' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'section_id' => ['required', 'exists:sections,id'],
            'branch_id' => [$isGlobal ? 'nullable' : 'required', Rule::exists('branches', 'id')->where('is_active', true)],
            'shift_id' => ['required', Rule::exists('shifts', 'id')->where('status', 'open')],
            'status' => ['required', 'in:active,inactive'],
            'hire_date' => ['required', 'date'],
            'base_salary' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'employee_number' => ['nullable', 'string', 'max:40', Rule::unique('employees', 'employee_number')->ignore($employeeId)],
            'national_id' => ['nullable', 'string', 'max:40', Rule::unique('employees', 'national_id')->ignore($employeeId)],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:ذكر,أنثى'],
            'address' => ['nullable', 'string', 'max:2000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:180'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'education_level' => ['nullable', 'string', 'max:100'],
            'employment_type' => ['required', 'in:دوام كامل,دوام جزئي,موسمي,مؤقت'],
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'manager_employee_id' => ['nullable', 'exists:employees,id'],
            'insurance_wage' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'insurance_enabled' => ['required', 'boolean'],
            'tax_enabled' => ['required', 'boolean'],
            'payroll_bank_name' => ['nullable', 'string', 'max:120'],
            'payroll_bank_account' => ['nullable', 'string', 'max:80'],
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('employees', 'user_id')->ignore($employeeId)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'حقل الاسم الكامل مطلوب.',
            'name.max' => 'يجب ألا يتجاوز الاسم 255 حرفاً.',
            'email.required' => 'حقل البريد الإلكتروني مطلوب.',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل لموظف آخر.',
            'position.required' => 'حقل المسمى الوظيفي مطلوب.',
            'phone.required' => 'حقل رقم الهاتف مطلوب.',
            'phone.max' => 'رقم الهاتف أطول من المسموح به (الحد الأقصى 30 حرفاً).',
            'section_id.required' => 'يرجى اختيار القسم.',
            'section_id.exists' => 'القسم المختار غير صالح.',
            'branch_id.required' => 'يرجى اختيار الفرع.',
            'shift_id.required' => 'يرجى اختيار الوردية.',
            'status.required' => 'يرجى تحديد حالة الموظف.',
            'hire_date.required' => 'يرجى تحديد تاريخ التعيين.',
            'base_salary.required' => 'يرجى إدخال الراتب الأساسي.',
            'base_salary.numeric' => 'الراتب الأساسي يجب أن يكون رقماً.',
            'employee_number.max' => 'الرقم الوظيفي يجب ألا يتجاوز 40 حرفاً.',
            'employee_number.unique' => 'الرقم الوظيفي مسجل مسبقاً لموظف آخر.',
            'national_id.max' => 'رقم الهوية يجب ألا يتجاوز 40 حرفاً.',
            'national_id.unique' => 'رقم الهوية مسجل مسبقاً لموظف آخر.',
            'date_of_birth.before' => 'تاريخ الميلاد يجب أن يكون قبل تاريخ اليوم.',
            'contract_end_date.after_or_equal' => 'تاريخ نهاية العقد يجب أن يكون بعد أو مساوياً لتاريخ التعيين.',
            'user_id.unique' => 'هذا المستخدم مرتبط بالفعل بموظف آخر.',
        ];
    }
}

