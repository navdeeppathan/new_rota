<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\User;


class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Task::query(); 
    
            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }
    
            $schedule = $query->latest()->get();
    
            return view('admin.schedule.index', compact('schedule'));
        } catch (\Exception $e) {
            Log::error('Schedule Index Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while loading schedules.');
        }
    }
    
    public function create()
    {
         $users = User::all(); 
        return view('admin.schedule.create', compact('users'));
    }
    
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
    
            $start = Carbon::createFromFormat('H:i', $validatedData['start_time']);
            $end = Carbon::createFromFormat('H:i', $validatedData['end_time']);
            $minutes = $end->diffInMinutes($start);
            $validatedData['duration_hours'] = floatval(number_format(floor($minutes / 60) + ($minutes % 60 / 100), 2));
    
            Task::create($validatedData);
    
            return redirect()->route('schedule.index')->with('success', 'Schedule created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    public function edit($id)
    {
        $schedule = Task::findOrFail($id);
        $users = User::all();
        return view('admin.schedule.edit', compact('schedule', 'users'));
    }
    
    public function update(Request $request, $id)
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
    
            $start = Carbon::createFromFormat('H:i', $validatedData['start_time']);
            $end = Carbon::createFromFormat('H:i', $validatedData['end_time']);
            $minutes = $end->diffInMinutes($start);
            $validatedData['duration_hours'] = floatval(number_format(floor($minutes / 60) + ($minutes % 60 / 100), 2));
    
            $task = Task::findOrFail($id);
            $task->update($validatedData);
    
            return redirect()->route('schedule.index')->with('success', 'Schedule updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    public function destroy($id)
    {
        Task::findOrFail($id)->delete();
        return redirect()->route('schedule.index')->with('success', 'Schedule deleted.');
    }

}