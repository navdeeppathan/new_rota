<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskImage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ScheduleTask;

class TaskController extends Controller
{
    // Get all tasks with images and user
    public function index(Request $request)
    {
        try {
            $userId = $request->query('user_id');
    
            $query = Task::with('user')->orderBy('id', 'desc');
    
            if ($userId) {
                $query->where('user_id', $userId);
            }
    
            $tasks = $query->get();
    
            return response()->json([
                'success' => true,
                'data' => $tasks,
            ]);
        } catch (\Exception $e) {
            Log::error('Task index error: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tasks',
            ], 500);
        }
    }


    // Store new task and images
   public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id'     => 'required|exists:users,id',
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'location'    => 'nullable|string|max:255',
                'date'        => 'required|date',
                'start_time'  => 'required|date_format:H:i',
                'end_time'    => 'required|date_format:H:i|after:start_time',
            ]);
    
            // Calculate duration in decimal (e.g., 1.30 for 1 hr 30 min)
            $start = Carbon::createFromFormat('H:i', $validatedData['start_time']);
            $end = Carbon::createFromFormat('H:i', $validatedData['end_time']);
            $diffInMinutes = $end->diffInMinutes($start);
    
            $hours = floor($diffInMinutes / 60);
            $minutes = $diffInMinutes % 60;
    
            // Convert to float format like 1.30
            $validatedData['duration_hours'] = floatval(number_format($hours + ($minutes / 100), 2));
    
            // Create task
            $task = Task::create($validatedData);
    
            return response()->json([
                'success' => true,
                'data'    => $task,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Task store error: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to store task.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    

    // Show a specific task with its images and user
    public function show($id)
    {
        try {
            $task = Task::with('images', 'user')->find($id);

            if (!$task) {
                return response()->json(['success' => false, 'message' => 'Task not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $task]);
        } catch (\Exception $e) {
            Log::error('Task show error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch task']);
        }
    }

    // Update a task (images not updated here)
    public function update(Request $request, $id)
    {
        
        try {
            $task = Task::find($id);

            if (!$task) {
                return response()->json(['success' => false, 'message' => 'Task not found'], 404);
            }

            $task->update($request->only([
                'user_id', 'title', 'description', 'location',
                'date', 'start_time', 'end_time', 'duration_hours'
            ]));

            return response()->json(['success' => true, 'data' => $task->load('images')]);
        } catch (\Exception $e) {
            Log::error('Task update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update task']);
        }
    }

    // Delete task and its images
    public function destroy($id)
    {
        
        
        try {
            $task = Task::find($id);

            if (!$task) {
                return response()->json(['success' => false, 'message' => 'Task not found'], 404);
            }

            // Delete image files from public/task_images
            foreach ($task->images as $img) {
                $fullPath = public_path($img->file_path);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                $img->delete();
            }

            $task->delete();

            return response()->json(['success' => true, 'message' => 'Task deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Task delete error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete task']);
        }
    }
    
    public function getTasksByUserId(Request $request)
    {
       
        try {
            $id=$request->input('user_id');
            
    // dd($request);
            // $tasks = Task::where('user_id', $id)->get();
            
             $tasks = Task::with(['performances.images'])
                          ->where('user_id', $id)
                          ->get();
    
            return response()->json([
                'success' => true,
                'message' => 'Tasks fetched successfully.',
                'data'    => $tasks,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error fetching tasks by user: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tasks.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    
    // public function getTasksByUserDate(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'user_id' => 'required|exists:users,id',
    //             'date'    => 'required|date',
    //         ]);
    
    //       $tasks = Task::with(['performances.images'])
    //                       ->where('user_id', $validated['user_id'])
    //                       ->get();
    
    //         if (!empty($validated['date'])) {
    //             $query->whereDate('date', $validated['date']);
    //         }
    
    //         $tasks = $query->get();
    
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Tasks fetched successfully.',
    //             'data'    => $tasks,
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed.',
    //             'errors'  => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         Log::error('Error fetching tasks by user: ' . $e->getMessage());
    
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch tasks.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function getTasksByUserDate(Request $request)
{
    try {
        $user_id = $request->input('user_id');
        $date    = $request->input('date'); // Format: Y-m-d

        // Validate required inputs
        if (!$user_id || !$date) {
            return response()->json([
                'status' => false,
                'message' => 'Both user_id and date are required.'
            ], 422);
        }

        // Get all schedule_task records for the user and date
        $performances = ScheduleTask::with(['task', 'schedule.images'])
            ->where('user_id', $user_id)
            ->whereHas('task', function ($query) use ($date) {
                $query->whereDate('date', $date);
            })
            ->get();

        // Group performances by task_id
        $tasks = $performances->groupBy('task_id')->map(function ($items) {
            $task = $items->first()->task;

            return [
                'id'              => $task->id,
                'user_id'         => $task->user_id,
                'title'           => $task->title,
                'description'     => $task->description,
                'location'        => $task->location,
                'date'            => $task->date,
                'start_time'      => $task->start_time,
                'end_time'        => $task->end_time,
                'duration_hours'  => $task->duration_hours,
                'created_at'      => $task->created_at,
                'updated_at'      => $task->updated_at,
                'schedules'       => $items->map(function ($perf) {
                    return [
                        'id'             => $perf->schedule->id,
                        'images'         => $perf->images,
                        'title'          => $perf->schedule->title,
                        'date'           => $perf->schedule->date,
                        'start_time'     => $perf->schedule->start_time,
                        'end_time'       => $perf->schedule->end_time,
                        'duration_hours' => $perf->schedule->duration_hours,
                        'icon_path'      => $perf->schedule->icon_path,
                        'created_at'     => $perf->schedule->created_at,
                        'updated_at'     => $perf->schedule->updated_at,
                    ];
                })->values()
            ];
        })->values();

        return response()->json([
            'status'  => true,
            'message' => "Tasks with their schedules for user {$user_id} on {$date}",
            'tasks'   => $tasks
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

    
  public function getUserTasksWithPerformance(Request $request)
{
    try {
        $user_id = $request->input('user_id');

        // Get all schedule_task records for the user (with task & schedule)
        $performances = ScheduleTask::with(['task', 'schedule.images'])
            ->where('user_id', $user_id)
            ->get();

        // Group performances by task_id
        $tasks = $performances->groupBy('task_id')->map(function ($items) {
            $task = $items->first()->task;

            return [
                'id'              => $task->id,
                'user_id'         => $task->user_id,
                'title'           => $task->title,
                'description'     => $task->description,
                'location'        => $task->location,
                'date'            => $task->date,
                'start_time'      => $task->start_time,
                'end_time'        => $task->end_time,
                'duration_hours'  => $task->duration_hours,
                'created_at'      => $task->created_at,
                'updated_at'      => $task->updated_at,
                'schedules'       => $items->map(function ($perf) {
                    return [
                        
                            'id'             => $perf->schedule->id,
                            'images'         => $perf->images,
                            'title'          => $perf->schedule->title,
                            'date'           => $perf->schedule->date,
                            'start_time'     => $perf->schedule->start_time,
                            'end_time'       => $perf->schedule->end_time,
                            
                            'duration_hours' => $perf->schedule->duration_hours,
                            'icon_path'      => $perf->schedule->icon_path,
                            'created_at'     => $perf->schedule->created_at,
                            'updated_at'     => $perf->schedule->updated_at,
                        
                    ];
                })->values()
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Tasks with their schedules for user ' . $user_id,
            'tasks' => $tasks
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}


}
