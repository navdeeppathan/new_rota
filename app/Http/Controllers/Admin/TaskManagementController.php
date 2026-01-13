<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskManagement;
use Illuminate\Http\Request;

class TaskManagementController extends Controller
{
    public function index()
    {
        $allTasks = TaskManagement::all();

        $safe       = $allTasks->where('section','Safe')->count();
        $effective  = $allTasks->where('section','Effective')->count();
        $wellled    = $allTasks->where('section','Well-Led')->count();
        $responsive = $allTasks->where('section','Responsive')->count();
        $caring     = $allTasks->where('section','Caring')->count();

        $remaining = $allTasks->where('progress','!=','completed')->count();

        function buildCqcStats($tasks) {
            return [
                'yes' => $tasks->where('progress','completed')->count(),
                'no'  => $tasks->where('progress','not completed')->count(),
                'na'  => $tasks->whereIn('progress',['progress','note','location'])->count(),
            ];
        }

        $safeOriginal       = buildCqcStats($allTasks->where('section','Safe'));
        $effectiveOriginal  = buildCqcStats($allTasks->where('section','Effective'));
        $wellledOriginal    = buildCqcStats($allTasks->where('section','Well-Led'));
        $responsiveOriginal = buildCqcStats($allTasks->where('section','Responsive'));
        $caringOriginal     = buildCqcStats($allTasks->where('section','Caring'));

        return view('admin.cqc.tasks.dashboard', compact(
            'safe','effective','wellled','responsive','caring','remaining',
            'safeOriginal','effectiveOriginal','wellledOriginal','responsiveOriginal','caringOriginal'
        ));
    }




 public function index2()
{
   $safe = TaskManagement::where('section','Safe')
    ->latest()
    ->paginate(10, ['*'], 'safe');


   $effective = TaskManagement::where('section','Effective')
    ->latest()
    ->paginate(10, ['*'], 'effective');

$caring = TaskManagement::where('section','Caring')
    ->latest()
    ->paginate(10, ['*'], 'caring');

$responsive = TaskManagement::where('section','Responsive')
    ->latest()
    ->paginate(10, ['*'], 'responsive');

$wellLed = TaskManagement::where('section','Well-Led')
    ->latest()
    ->paginate(10, ['*'], 'wellled');


    return view('admin.cqc.tasks.index', compact(
        'safe','effective','caring','responsive','wellLed'
    ));
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

        public function edit($id)
    {
        $task = TaskManagement::findOrFail($id);
        return view('admin.cqc.tasks.edit', compact('task'));
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
            $task->progress_desc = $request->progress_desc;
        }

        $task->save();

        return redirect()->back()->with('success', 'Task updated successfully!');
    }

    public function updateProgress(Request $request, $id)
{
    $task = TaskManagement::findOrFail($id);

    $task->progress = $request->progress; // note or location
    $task->progress_desc = $request->progress_desc;
    $task->save();

    return response()->json(['success' => true]);
}



    public function destroy($id)
    {
        TaskManagement::destroy($id);
        return redirect()->back()->with('success', 'Task deleted!');
    }
}
