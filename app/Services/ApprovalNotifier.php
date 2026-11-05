<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\HrmsNotice;
use Illuminate\Support\Facades\Schema;

class ApprovalNotifier
{
    public function branchApprovers(?int $branchId, string $permission, string $title, string $message, string $url, ?int $excludeUserId = null): int
    {
        if (!Schema::hasTable('notifications')) return 0;
        $access = app(BranchAccess::class);
        $sent = 0;
        foreach (User::permission($permission)->get() as $user) {
            if ($user->id === $excludeUserId) continue;
            if (!$access->isGlobal($user) && (!$branchId || !in_array($branchId, $access->branchIds($user), true))) continue;
            $user->notify(new HrmsNotice($title, $message, $url, 'موافقة'));
            $sent++;
        }
        return $sent;
    }

    public function permissionUsers(string $permission, string $title, string $message, string $url, ?int $branchId = null): int
    {
        if (!Schema::hasTable('notifications')) return 0;
        $access = app(BranchAccess::class);
        $sent = 0;
        foreach (User::permission($permission)->get() as $user) {
            if ($branchId && !$access->isGlobal($user) && !in_array($branchId, $access->branchIds($user), true)) continue;
            $user->notify(new HrmsNotice($title, $message, $url));
            $sent++;
        }
        return $sent;
    }
}
