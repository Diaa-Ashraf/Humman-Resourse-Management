<?php

namespace App\Http\Controllers;

use App\Models\DataRetentionPolicy;
use Illuminate\Http\Request;

class RetentionPolicyController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->can('manage_retention'), 403);
        $policies = DataRetentionPolicy::orderBy('id')->get();
        return view('dashboard.governance.retention', compact('policies'));
    }

    public function update(Request $request, DataRetentionPolicy $policy)
    {
        abort_unless($request->user()->can('manage_retention'), 403);
        $values = $request->validate(['retention_days' => ['required', 'integer', 'between:1,36500'], 'cleanup_enabled' => ['nullable', 'boolean'], 'legal_basis' => ['required', 'string', 'max:4000']]);
        $policy->update(['retention_days' => $values['retention_days'], 'cleanup_enabled' => $request->boolean('cleanup_enabled'), 'legal_basis' => $values['legal_basis'], 'updated_by' => $request->user()->id]);
        return back()->with('success', 'تم تحديث سياسة الاحتفاظ.');
    }
}
