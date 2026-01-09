<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShiftDefinition;
use Illuminate\Support\Facades\Log;
use App\Models\User;


class ShiftDefinitionController extends Controller
{
    
    public function create()
    {
        $admin = User::where('role_id', 1)->first(); // Get first admin
    
        return view('admin.shift_definitions.create', compact('admin'));
    }


  public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'shift_slot'  => 'nullable|string|max:20',
            'day_start'   => 'nullable|string|max:10',
            'day_end'     => 'nullable|string|max:10',
            'night_start' => 'nullable|string|max:10',
            'night_end'   => 'nullable|string|max:10',
            'break_start' => 'nullable|string|max:10',
            'break_end'   => 'nullable|string|max:10',
        ]);

        // Convert time strings to Carbon instances
        $breakStart = $validated['break_start'] ? \Carbon\Carbon::createFromFormat('H:i', $validated['break_start']) : null;
        $breakEnd   = $validated['break_end']   ? \Carbon\Carbon::createFromFormat('H:i', $validated['break_end'])   : null;

        $breakDuration = null;
        if ($breakStart && $breakEnd && $breakEnd->gt($breakStart)) {
            $diff = $breakStart->diff($breakEnd);
            $breakDuration = $diff->format('%h hr %i min');
        }

        // Determine total working time (based on shift_slot)
        $workingDuration = null;
        if ($validated['shift_slot'] === 'Day' && $validated['day_start'] && $validated['day_end']) {
            $start = \Carbon\Carbon::createFromFormat('H:i', $validated['day_start']);
            $end   = \Carbon\Carbon::createFromFormat('H:i', $validated['day_end']);
            if ($end->gt($start)) {
                $workingDuration = $start->diff($end)->format('%h hr %i min');
            }
        } elseif ($validated['shift_slot'] === 'Night' && $validated['night_start'] && $validated['night_end']) {
            $start = \Carbon\Carbon::createFromFormat('H:i', $validated['night_start']);
            $end   = \Carbon\Carbon::createFromFormat('H:i', $validated['night_end']);

            // Handle overnight (e.g., 20:00 to 07:00 next day)
            if ($end->lte($start)) {
                $end->addDay();
            }
            $workingDuration = $start->diff($end)->format('%h hr %i min');
        }

        $data = array_merge($validated, [
            'break_duration'      => $breakDuration,
            'total_break_time'    => $breakDuration,
            'total_working_time'  => $workingDuration,
        ]);

       $shiftDefinition = ShiftDefinition::create($data);


        return redirect()->route('shift-definitions.create')->with('success', 'Shift definition saved successfully.');

    } catch (\Exception $e) {
        Log::error('Error storing shift definition: ' . $e->getMessage());

        return response()->json([
            'status' => false,
            'message' => 'Failed to save shift definition.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    
    public function getByUserId(Request $request)
{
    $userId = $request->query('user_id');

    if (!$userId) {
        return response()->json([
            'status' => false,
            'message' => 'user_id is required'
        ], 400);
    }

    $shift = ShiftDefinition::where('user_id', $userId)->first();

    if ($shift) {
        return response()->json([
            'status' => true,
            'data' => $shift
        ]);
    } else {
        return response()->json([
            'status' => false,
            'message' => 'No shift definition found for this user.'
        ]);
    }
}

}
