<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskManagement;
use Illuminate\Http\Request;

class TaskManagementController extends Controller
{
    public function index()
{
    $tasks = TaskManagement::latest()->paginate(5);   // 5 rows per page
    
    
    return view('admin.cqc.tasks.dashboard', compact(
        'tasks',
       
    ));
}


    public function index2()
    {
        $tasks = TaskManagement::latest()->get();
        return view('admin.cqc.tasks.index', compact('tasks'));
    }
    public function create()
    {
        return view('admin.cqc.tasks.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required',
            'section' => 'required',
        ]);

        TaskManagement::create([
            'description' => $request->description,
            'section' => $request->section,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Task added successfully!');
    }

    public function update(Request $request, $id)
    {
        $task = TaskManagement::findOrFail($id);

        $task->description = $request->description;
        $task->section = $request->section;
        $task->progress = $request->progress;

        // Only store description if Note or Location
        if (in_array($request->progress, ['note', 'location'])) {
            $task->progress_desc = $request->progress_desc;
        } else {
            $task->progress_desc = null;
        }

        $task->save();

        return redirect()->back()->with('success', 'Task updated successfully!');
    }


    public function destroy($id)
    {
        TaskManagement::destroy($id);
        return redirect()->back()->with('success', 'Task deleted!');
    }
}
