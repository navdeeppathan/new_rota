<?php

namespace App\Http\Controllers\Api;

use App\Models\Shift;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShiftController extends Controller
{
    public function index()
    {
        try {
            $shifts = Shift::with(['shiftType', 'user'])->get();
            return response()->json(['success' => true, 'message' => 'Shifts fetched successfully', 'data' => $shifts]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch shifts', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'shift_date' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required',
                'shift_type_id' => 'nullable|exists:shift_types,id',
                'is_active' => 'nullable|boolean',
            ]);

            $shift = Shift::create($request->all());
            return response()->json(['success' => true, 'message' => 'Shift created', 'data' => $shift->load('shiftType', 'user')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create shift', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $id=$request->input('id');

            $shift = Shift::findOrFail($id);
            $shift->update($request->all());
            return response()->json(['success' => true, 'message' => 'Shift updated', 'data' => $shift->load('shiftType', 'user')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update shift', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id=$request->input('id');

            $shift = Shift::findOrFail($id);
            $shift->delete();
            return response()->json(['success' => true, 'message' => 'Shift deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete shift', 'error' => $e->getMessage()], 500);
        }
    }
}

