<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ComplianceTask;
use App\Models\ComplianceCheck;
use Carbon\Carbon;

class ComplianceController extends Controller
{
    /**
     * Load Compliance Dashboard Page
     */
  public function index()
{
    $tasks = ComplianceTask::with('check')
        ->where('is_active', 1)
        ->get();
           // Daily report (TODAY)
    $dailyReport = ComplianceCheck::with('task')
        ->whereDate('checked_at', now()->toDateString())
        ->get();

    return view('admin.cqc.checklist_cqc', compact('tasks', 'dailyReport'));
}


    /**
     * Get checklist + score (API)
     */
    public function getChecklist()
    {
        $tasks = ComplianceTask::with('check')->where('is_active', 1)->get();

        $score = 0;
        foreach ($tasks as $task) {
            if ($task->check && $task->check->is_checked) {
                $score += $task->weight;
            }
        }

        return response()->json([
            'tasks' => $tasks,
            'score' => $score
        ]);
    }

    /**
     * Update checkbox state (AJAX)
     */
public function updateCheck(Request $request)
{
    $request->validate([
        'c_tasks_id' => 'required|exists:compliance_tasks,id',
        'is_checked' => 'required|boolean',
        'percent'    => 'nullable|numeric|min:0|max:100',
        'frequency'  => 'nullable|in:Daily,Weekly,Monthly'
    ]);

    if ($request->is_checked) {

        ComplianceCheck::updateOrCreate(
            ['c_tasks_id' => $request->c_tasks_id],
            [
                'is_checked' => 1,
                'percent'    => $request->percent,
                'frequency'  => $request->frequency,
                'checked_at' => now(),
                'checked_by' => session('user.name') ?? 'System'
            ]
        );

    } else {
        ComplianceCheck::where('c_tasks_id', $request->c_tasks_id)->delete();
    }

    // ✅ CORRECT WEIGHTED SCORE
    $totalTasks   = ComplianceTask::where('is_active', 1)->count();
    $taskWeight   = 100 / $totalTasks;

    $score = 0;
    $checks = ComplianceCheck::get();

    foreach ($checks as $check) {
        $score += ($check->percent / 100) * $taskWeight;
    }

    return response()->json([
        'status' => true,
        'score'  => round(min($score, 100), 2)
    ]);
}




    /**
     * Reset checklist by frequency (CRON / Scheduler)
     */
    public function resetByFrequency($frequency)
    {
        $taskIds = ComplianceTask::where('frequency', $frequency)->pluck('id');

        ComplianceCheck::whereIn('c_tasks_id', $taskIds)->update([
            'is_checked' => 0,
            'checked_at' => null
        ]);

        return response()->json([
            'status' => true,
            'message' => "{$frequency} checklist reset successfully"
        ]);
    }
}
