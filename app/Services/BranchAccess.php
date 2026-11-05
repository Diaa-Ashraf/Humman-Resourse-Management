<?php
namespace App\Services;

use App\Models\{Employee,User};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class BranchAccess
{
    public function isGlobal(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->can('view_all_branches');
    }

    /** @return array<int> */
    public function branchIds(User $user): array
    {
        $assigned=$user->branchScopes()->pluck('branches.id')->map(fn($id)=>(int)$id)->all();
        if($assigned)return $assigned;
        $employeeBranch=$user->employee?->branch_id;
        return $employeeBranch ? [(int)$employeeBranch] : [];
    }

    public function employees(Builder|Relation $query,User $user,string $column='branch_id'): Builder|Relation
    {
        if($this->isGlobal($user))return $query;
        $ids=$this->branchIds($user);
        return $ids ? $query->whereIn($column,$ids) : $query->whereRaw('1 = 0');
    }

    public function throughEmployee(Builder|Relation $query,User $user,string $relation='employee'): Builder|Relation
    {
        if($this->isGlobal($user))return $query;
        $ids=$this->branchIds($user);
        return $ids ? $query->whereHas($relation,fn($employee)=>$employee->whereIn('branch_id',$ids)) : $query->whereRaw('1 = 0');
    }

    public function assertEmployee(Employee $employee,User $user): void
    {
        abort_unless($this->isGlobal($user)||in_array((int)$employee->branch_id,$this->branchIds($user),true),403,'هذا الموظف خارج نطاق الفروع المصرح لك بها.');
    }

    public function assertBranch(?int $branchId,User $user): void
    {
        abort_unless($this->isGlobal($user)||($branchId!==null&&in_array($branchId,$this->branchIds($user),true)),403,'الفرع خارج نطاق الصلاحية المحدد لحسابك.');
    }
}
