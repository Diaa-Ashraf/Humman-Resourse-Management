<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Rules\StrongPassword;
use App\Models\UserSession;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), new StrongPassword, 'confirmed'],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']), 'password_changed_at' => now(),
        ])->save();
        $sessionId = $request->session()->getId();
        UserSession::where('user_id', $request->user()->id)->where('session_id', '!=', $sessionId)->whereNull('revoked_at')->update(['revoked_at' => now()]);
        $sessionsTable = config('session.table', 'sessions');
        if (\Illuminate\Support\Facades\Schema::hasTable($sessionsTable) && \Illuminate\Support\Facades\Schema::hasColumn($sessionsTable, 'user_id')) {
            \Illuminate\Support\Facades\DB::table($sessionsTable)->where('user_id', $request->user()->id)->where('id', '!=', $sessionId)->delete();
        }

        return back()->with('status', 'password-updated');
    }
}
