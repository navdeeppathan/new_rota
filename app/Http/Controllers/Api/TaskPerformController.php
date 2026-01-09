<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TaskPerform;
use App\Models\Task;
use App\Models\TaskImage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ScheduleTask;

class TaskPerformController extends Controller
{
    // Store single performance with optional image upload
    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'task_id'     => 'nullable|exists:tasks,id',
                'user_id'     => 'nullable|exists:users,id',
                'title'       => 'required|string|max:255',
                'date'        => 'nullable|date',
                'start_time'  => 'nullable|date_format:H:i',
                'end_time'    => 'nullable|date_format:H:i|after:start_time',
                'images'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
    
            // Calculate and format duration as HH.MM
            if (!empty($validated['start_time']) && !empty($validated['end_time'])) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time']);
                $end = \Carbon\Carbon::createFromFormat('H:i', $validated['end_time']);
                $minutes = $end->diffInMinutes($start);
    
                $hours = floor($minutes / 60);
                $mins = $minutes % 60;
    
                // Format like 1.30 for 1 hr 30 min or 0.30 for 30 min
                $validated['duration_hours'] = floatval($hours . '.' . str_pad($mins, 2, '0', STR_PAD_LEFT));
            }
    
            // Create TaskPerform record
            $record = TaskPerform::create($validated);
    
            // Handle optional image upload
            if ($request->hasFile('images')) {
                $image = $request->file('images');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('task_images');
    
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
    
                $image->move($destinationPath, $filename);
    
                TaskImage::create([
                    'task_id'   => $record->id, // Save as task_perform id if images are linked that way
                    'user_id'   => $record->user_id,
                    'file_path' => 'task_images/' . $filename,
                ]);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Task performance saved successfully.',
                'data'    => $record,
            ]);
        } catch (\Exception $e) {
            Log::error('TaskPerform store error: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to save task performance.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    

    // Update/Create multiple task performances
   
    
    public function updateMultipleWithSharedTime(Request $request)
    {
        try {
            $validated = $request->validate([
                'task_ids'      => 'required|array|min:1',
                'task_ids.*'    => 'required|exists:tasks,id',
                'user_ids'      => 'required|array|min:1',
                'user_ids.*'    => 'required|exists:users,id',
                'start_time'    => 'nullable|date_format:H:i',
                'end_time'      => 'nullable|date_format:H:i|after:start_time',
            ]);
    
            // Calculate shared duration
            $start = Carbon::createFromFormat('H:i', $validated['start_time']);
            $end = Carbon::createFromFormat('H:i', $validated['end_time']);
            $minutes = $end->diffInMinutes($start);
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
    
            $durationText = '';
            if ($hours > 0) {
                $durationText .= $hours . ' hr' . ($hours > 1 ? 's' : '');
            }
            if ($remainingMinutes > 0) {
                if ($hours > 0) $durationText .= ' ';
                $durationText .= $remainingMinutes . ' min';
            }
            if ($durationText === '') {
                $durationText = '0 min';
            }
    
            // Loop over combinations
            $results = [];
    
            foreach ($validated['task_ids'] as $taskId) {
                foreach ($validated['user_ids'] as $userId) {
                    $taskPerform = TaskPerform::where('task_id', $taskId)
                        ->where('user_id', $userId)
                        ->first();
    
                    if (!$taskPerform) {
                        $results[] = [
                            'task_id' => $taskId,
                            'user_id' => $userId,
                            'status'  => 'not found',
                        ];
                        continue;
                    }
    
                    $taskPerform->start_time = $validated['start_time'];
                    $taskPerform->end_time = $validated['end_time'];
                    $taskPerform->duration_hours = $minutes / 60;
                    $taskPerform->save();
    
                    $results[] = [
                        'task_id' => $taskId,
                        'user_id' => $userId,
                        'status'  => 'updated',
                        'duration' => $durationText,
                    ];
                }
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Task performances updated successfully.',
                'data'    => $results,
            ]);
    
        } catch (\Exception $e) {
            Log::error('Shared TaskPerform update error: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task performances.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    
    public function getTodayTaskPerformances(Request $request)
    {
        try {
            $id=$request->input('user_id');
               
            
    
            $today = Carbon::today()->toDateString();
    
            $tasks = TaskPerform::with('images')
                ->where('user_id', $id)
                ->whereDate('date', $today)
                ->get();
    
            
            return response()->json([
                'success' => true,
                'message' => 'Today\'s task performances fetched successfully.',
                'data'    => $tasks,
            ]);
    
        } catch (\Exception $e) {
            Log::error('Error fetching today\'s tasks: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch today\'s task performances.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    
    public function getTaskPerformancesByDate(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'date'    => 'required|date', 
            ]);
    
            $date = $validated['date'];
    
            $tasks = TaskPerform::with('images')
                ->where('user_id', $validated['user_id'])
                ->whereDate('date', $date)
                ->get();
    
            return response()->json([
                'success' => true,
                'message' => "Task performances for " . Carbon::parse($date)->toFormattedDateString(),
                'data'    => $tasks,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error fetching task performances by date: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch task performances.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    
    
    public function getScheduleTasksByDate(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'date'    => 'required|date',
            ]);
    
            $date = $validated['date'];
    

            $scheduleTasks = ScheduleTask::with('schedule.images')
                ->where('user_id', $validated['user_id'])
                ->whereDate('scheduled_date', $date)
                ->get()
                ->pluck('schedule');
    
            return response()->json([
                'success' => true,
                'message' => "Schedule tasks for " . Carbon::parse($date)->toFormattedDateString(),
                'data'    => $scheduleTasks,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error fetching schedule tasks by date: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch schedule tasks.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

        
}
