<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvailabilityCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class AvailabilityCalendarController extends Controller
{
     public function index(Request $request)
    {
        try {
            
            
             $availabilities = AvailabilityCalendar::with('user')
            ->orderBy('created_at', 'desc')
            ->get();
             
            return view('availability.index', compact('availabilities'));
            
        } catch (\Exception $e) {
            
            Log::error('AvailabilityCalendar fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch availability.',
                'error' => $e->getMessage()
            ], 500);
            
        }
    }
    // Store or Update availability
    // public function store(Request $request)
    // {
    //     try {
    //         $entries = $request->input('availabilities');
    
    //         if (!is_array($entries) || empty($entries)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid or empty availability data'
    //             ], 422);
    //         }
    
    //         $inserted = 0;
    //         $updated = 0;
    
    //         foreach ($entries as $entry) {
    //             $validated = validator($entry, [
    //                 'user_id'    => 'required|integer|exists:users,id',
    //                 'date'       => 'required|date',
    //                 'start_time' => 'nullable|date_format:H:i:s',
    //                 'end_time'   => 'nullable|date_format:H:i:s',
    //                 'is_day_off' => 'required|boolean',
    //             ])->validate();
    
    //             $validated['month'] = Carbon::parse($validated['date'])->format('Y-m');
    
    //             $availability = AvailabilityCalendar::updateOrCreate(
    //                 ['user_id' => $validated['user_id'], 'date' => $validated['date']],
    //                 $validated
    //             );
    
    //             $availability->wasRecentlyCreated ? $inserted++ : $updated++;
    //         }
             
    //         $availabilities = AvailabilityCalendar::orderBy('created_at', 'desc')->get();
    //         return view('availability.index', compact('availabilities'));
            
    //         // return redirect()->back()->with('success', 'Availability saved successfully!');

    
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Availability stored successfully.',
    //             'inserted' => $inserted,
    //             'updated' => $updated
    //         ]);
    //     } catch (\Exception $e) {
            
    //         Log::error('AvailabilityCalendar bulk store error: ' . $e->getMessage());
            
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to store availability.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    
public function store(Request $request)
{
    try {
        $entries = $request->input('availabilities');

        if (!is_array($entries) || empty($entries)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or empty availability data'
            ], 422);
        }

        $inserted = 0;
        $updated = 0;

        foreach ($entries as $entry) {
            $validated = validator($entry, [
                'user_id'    => 'required|integer|exists:users,id',
               
                'start_time' => 'nullable|date_format:H:i:s',
                'end_time'   => 'nullable|date_format:H:i:s',
                'is_day_off' => 'required|boolean',
                'days'       => 'required|array', 
                'days.*'     => 'string|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'shift'      => 'required|string|max:20'
            ])->validate();

            // Convert days array to JSON for storage
            $validated['days'] = json_encode($validated['days']);

            // Store without using date/month
            $availability = AvailabilityCalendar::updateOrCreate(
                ['user_id' => $validated['user_id'], 'shift' => $validated['shift']],
                $validated
            );

            $availability->wasRecentlyCreated ? $inserted++ : $updated++;
        }

        // $availabilities = AvailabilityCalendar::orderBy('created_at', 'desc')->get();
        // return view('availability.index', compact('availabilities'));

        // If you want JSON API response:
        return response()->json([
            'success' => true,
            'message' => 'Availability stored successfully.',
            'inserted' => $inserted,
            'updated' => $updated
        ]);

    } catch (\Exception $e) {
        Log::error('AvailabilityCalendar bulk store error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to store availability.',
            'error' => $e->getMessage()
        ], 500);
    }
}



    // Get availability for a user and month
    public function getUserAvailability(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer',
                'month'   => 'required|date_format:Y-m'
            ]);

            $availabilities = AvailabilityCalendar::where('user_id', $request->input('user_id'))->get();

            return response()->json([
                'success' => true,
                'availabilities' => $availabilities
            ]);
        } catch (\Exception $e) {
            Log::error('AvailabilityCalendar fetch error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch availability.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
