<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    
    // // GET API: Fetch all broadcasts
    // public function getAllBroadcasts()
    // {
    //     try {
    //         $broadcasts = Broadcast::latest()->get();
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Broadcasts fetched successfully',
    //             'data' => $broadcasts
    //         ], 200);
    //     } catch (\Exception $e) {
    //          Log::error('Failed to fetch broadcasts', ['error' => $e->getMessage()]);
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Error fetching broadcasts',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function getAllBroadcasts()
    {
        try {
            $broadcasts = Broadcast::latest()->get();
    
            // Convert is_starred to string
            $broadcasts->transform(function ($b) {
                if (isset($b->is_starred)) {
                    $b->is_starred = (string) $b->is_starred;
                }
                return $b;
            });
    
            return response()->json([
                'status'  => true,
                'message' => 'Broadcasts fetched successfully',
                'data'    => $broadcasts
            ], 200);
    
        } catch (\Exception $e) {
            Log::error('Failed to fetch broadcasts', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Error fetching broadcasts',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    // Show list of broadcasts
    public function index()
    {
        try {
            $broadcasts = Broadcast::latest()->paginate(10);
            return view('broadcasts.index', compact('broadcasts'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load broadcasts: ' . $e->getMessage());
        }
    }

    // Show create form
    public function create()
    {
        return view('broadcasts.create');
    }

    // Store a new broadcast
    // public function store(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'title' => 'required|string|max:255',
    //             'description' => 'nullable|string',
    //             'broadcast_date' => 'nullable|date',
    //             'is_starred'=> 'nullable'
    //         ]);

    //         Broadcast::create($request->only(['title', 'description', 'broadcast_date','is_starred']));

    //         return redirect()->route('broadcasts.index')->with('success', 'Broadcast created successfully.');
    //     } catch (\Exception $e) {
    //         return back()->withInput()->with('error', 'Failed to create broadcast: ' . $e->getMessage());
    //     }
    // }
    
     public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'broadcast_date' => 'nullable|date',
                'is_starred' => 'nullable'
            ]);

            // If this is starred, remove starred flag from others
            if ($request->has('is_starred') && $request->is_starred) {
                Broadcast::where('is_starred', true)->update(['is_starred' => null]);
            }

            // Create the new broadcast
            Broadcast::create([
                'title' => $request->title,
                'description' => $request->description,
                'broadcast_date' => $request->broadcast_date,
                'is_starred' => $request->has('is_starred') ? true : null,
            ]);

            return redirect()->route('broadcasts.index')->with('success', 'Broadcast created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create broadcast: ' . $e->getMessage());
        }
    }


    // Show edit form
    public function edit($id)
    {
        try {
            $broadcast = Broadcast::findOrFail($id);
            return view('broadcasts.edit', compact('broadcast'));
        } catch (\Exception $e) {
            return back()->with('error', 'Broadcast not found: ' . $e->getMessage());
        }
    }

    // Update an existing broadcast
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'broadcast_date' => 'nullable|date',
                 'is_starred'=> 'nullable'
            ]);


            $broadcast = Broadcast::findOrFail($id);
            $broadcast->update([
                'title' => $request->title,
                'description' => $request->description,
                'broadcast_date' => $request->broadcast_date,
                'is_starred' => $request->has('is_starred') ? 1 : 0,
            ]);
            // $broadcast->update($request->only(['title', 'description', 'broadcast_date','is_starred']));

            return redirect()->route('broadcasts.index')->with('success', 'Broadcast updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update broadcast: ' . $e->getMessage());
        }
    }

    // Delete a broadcast
    public function destroy($id)
    {
        try {
            $broadcast = Broadcast::findOrFail($id);
            $broadcast->delete();

            return redirect()->route('broadcasts.index')->with('success', 'Broadcast deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete broadcast: ' . $e->getMessage());
        }
    }
}
