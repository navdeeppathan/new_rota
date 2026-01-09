<?php

namespace App\Http\Controllers;

use App\Models\TaskPerform;
use App\Models\TaskImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\ScheduleTask;



class TaskScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = TaskPerform::query();
    
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
    
        $tasks = $query->latest()->get();
    
        return view('admin.task_perform.index', compact('tasks'));
    }
    
    public function create()
    {
        return view('admin.task_perform.create');
    }
    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'date'        => 'nullable|date',
                'start_time'  => 'nullable|date_format:H:i',
                'end_time'    => 'nullable|date_format:H:i|after:start_time',
                'images'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
    
            if (!empty($validated['start_time']) && !empty($validated['end_time'])) {
                $start = Carbon::createFromFormat('H:i', $validated['start_time']);
                $end = Carbon::createFromFormat('H:i', $validated['end_time']);
                $minutes = $end->diffInMinutes($start);
                $validated['duration_hours'] = floatval(floor($minutes / 60) . '.' . str_pad($minutes % 60, 2, '0', STR_PAD_LEFT));
            }
    
            $task = TaskPerform::create($validated);
    
            if ($request->hasFile('images')) {
                $image = $request->file('images');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('task_images'), $filename);
    
                TaskImage::create([
                    'task_id' => $task->id,
                    'user_id' => $task->user_id,
                    'file_path' => 'task_images/' . $filename,
                ]);
            }
    
            return redirect()->route('task_perform.index')->with('success', 'Task saved successfully.');
    
        } catch (\Exception $e) {
            Log::error('Store error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong.');
        }
    }
    
    public function edit($id)
    {
        $task = TaskPerform::findOrFail($id);
        return view('admin.task_perform.edit', compact('task'));
    }
    
    public function update(Request $request, $id)
    {
        try {
            $task = TaskPerform::findOrFail($id);
    
            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'date'        => 'nullable|date',
                'start_time'  => 'nullable|date_format:H:i',
                'end_time'    => 'nullable|date_format:H:i|after:start_time',
                'images'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
    
            if (!empty($validated['start_time']) && !empty($validated['end_time'])) {
                $start = Carbon::createFromFormat('H:i', $validated['start_time']);
                $end = Carbon::createFromFormat('H:i', $validated['end_time']);
                $minutes = $end->diffInMinutes($start);
                $validated['duration_hours'] = floatval(floor($minutes / 60) . '.' . str_pad($minutes % 60, 2, '0', STR_PAD_LEFT));
            }
    
            $task->update($validated);
    
            if ($request->hasFile('images')) {
                $image = $request->file('images');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('task_images'), $filename);
    
                TaskImage::create([
                    'task_id' => $task->id,
                    'user_id' => $task->user_id,
                    'file_path' => 'task_images/' . $filename,
                ]);
            }
    
            return redirect()->route('task_perform.index')->with('success', 'Task updated successfully.');
    
        } catch (\Exception $e) {
            Log::error('Update error: ' . $e->getMessage());
            return back()->with('error', 'Update failed.');
        }
    }
    
   public function getByUser($user_id)
{
    $tasks = Task::where('user_id', $user_id)
        ->orderBy('date')
        ->get(['id', 'title', 'date']); // limit columns if needed

    return response()->json($tasks);
}

    
    public function assignView()
    {
        $schedules= TaskPerform::all();
        $users = User::all();
    
        return view('admin.task_perform.assign', compact('schedules', 'users'));
    }
    
    // public function updateMultipleWithSharedTime(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'task_ids'      => 'required|array|min:1',
    //             'task_ids.*'    => 'required|exists:tasks,id',
    //             'user_ids'      => 'required|array|min:1',
    //             'user_ids.*'    => 'required|exists:users,id',
    //             'start_time'    => 'nullable|date_format:H:i',
    //             'end_time'      => 'nullable|date_format:H:i|after:start_time',
    //         ]);
    
    //         // Calculate shared duration
    //         $start = Carbon::createFromFormat('H:i', $validated['start_time']);
    //         $end = Carbon::createFromFormat('H:i', $validated['end_time']);
    //         $minutes = $end->diffInMinutes($start);
    //         $hours = floor($minutes / 60);
    //         $remainingMinutes = $minutes % 60;
    
    //         $durationText = '';
    //         if ($hours > 0) {
    //             $durationText .= $hours . ' hr' . ($hours > 1 ? 's' : '');
    //         }
    //         if ($remainingMinutes > 0) {
    //             if ($hours > 0) $durationText .= ' ';
    //             $durationText .= $remainingMinutes . ' min';
    //         }
    //         if ($durationText === '') {
    //             $durationText = '0 min';
    //         }
    
    //         // Loop over combinations
    //         $results = [];
    
    //         foreach ($validated['task_ids'] as $taskId) {
    //             foreach ($validated['user_ids'] as $userId) {
    //                 $taskPerform = TaskPerform::where('task_id', $taskId)
    //                     ->where('user_id', $userId)
    //                     ->first();
    
    //                 if (!$taskPerform) {
    //                     $results[] = [
    //                         'task_id' => $taskId,
    //                         'user_id' => $userId,
    //                         'status'  => 'not found',
    //                     ];
    //                     continue;
    //                 }
    
    //                 $taskPerform->start_time = $validated['start_time'];
    //                 $taskPerform->end_time = $validated['end_time'];
    //                 $taskPerform->duration_hours = $minutes / 60;
    //                 $taskPerform->save();
    
    //                 $results[] = [
    //                     'task_id' => $taskId,
    //                     'user_id' => $userId,
    //                     'status'  => 'updated',
    //                     'duration' => $durationText,
    //                 ];
    //             }
    //         }
    
    //       return redirect()->back()->with('success', 'Task performances updated successfully.');

    
    //     } catch (\Exception $e) {
    //         Log::error('Shared TaskPerform update error: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Failed to update task performances.');
    //         // return response()->json([
    //         //     'success' => false,
    //         //     'message' => 'Failed to update task performances.',
    //         //     'error'   => $e->getMessage(),
    //         // ], 500);
    //     }
    // }


public function assignSchedulesToTask(Request $request)
{
    try {
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'schedule_ids' => 'required|array|min:1',
            'schedule_ids.*' => 'required|integer',
        ]);

        
        $task = Task::findOrFail($validated['task_id']);
        $userId = $validated['user_id'];
        $scheduleIds = $validated['schedule_ids'];
        $scheduledDate = $task->date; // assuming 'date' column in tasks

        foreach ($scheduleIds as $scheduleId) {
            $exists = ScheduleTask::where('task_id', $task->id)
                ->where('schedule_id', $scheduleId)
                ->where('user_id', $userId)
                ->exists();

            if (!$exists) {
                ScheduleTask::create([
                    'task_id'        => $task->id,
                    'schedule_id'    => $scheduleId,
                    'user_id'        => $userId,
                    'scheduled_date' => $scheduledDate,
                ]);
            }
        }
        
        return redirect()->back()->with('success', 'Schedules successfully assigned to task..');

        return response()->json([
            'message' => 'Schedules successfully assigned to task.',
            'task_id' => $taskId,
            'schedule_ids' => $scheduleIds,
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to assign schedules to task: ' . $e->getMessage());
        // return redirect()->back()->with('error', 'Failed to assign schedules to task.');
        return response()->json([
            'message' => 'Failed to assign schedules to task.',
            'error' => $e->getMessage(),
        ], 500);
    }
}



    
    public function destroy($id)
    {
        try {
            $task = TaskPerform::findOrFail($id);
            $task->delete();
    
            return redirect()->route('task_perform.index')->with('success', 'Deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Delete error: ' . $e->getMessage());
            return back()->with('error', 'Delete failed.');
        }
    }
}