<?php

namespace App\Http\Controllers\Api;

use App\Models\ShiftType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShiftTypeController extends Controller
{
    public function index()
    {
        try {
            $types = ShiftType::all();
            return response()->json(['success' => true, 'message' => 'Shift types fetched successfully', 'data' => $types]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch shift types', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string'
            ]);

            $type = ShiftType::create($request->only('name'));
            return response()->json(['success' => true, 'message' => 'Shift type created', 'data' => $type]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create shift type', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $id=$request->input('id');
            $type = ShiftType::findOrFail($id);
            $type->update($request->name);
            return response()->json(['success' => true, 'message' => 'Shift type updated', 'data' => $type]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update shift type', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id=$request->input('id');
            $type = ShiftType::findOrFail($id);
            $type->delete();
            return response()->json(['success' => true, 'message' => 'Shift type deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete shift type', 'error' => $e->getMessage()], 500);
        }
    }
}

