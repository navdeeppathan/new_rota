<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersonShift;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\User;
use App\Models\ShiftDefinition;
use Carbon\Carbon;
use App\Models\AvailabilityCalendar;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Model\LeaveRequest;

class PersonShiftController extends Controller
{
     private $shifts = [
        'LD' => ['07:15', '08:30'],
        'N1' => ['08:15', '19:30'],
        'N2' => ['08:30', '19:00'],
    ];

   public function create()
    {
        $startOfWeek = now()->startOfWeek();

        return view('shifts.create', [
            'kitchenUsers' => User::where('category', '1')->get(),
            'careUsers' => User::where('category', '2')->get(),
            'kitchenAvailabilities' => AvailabilityCalendar::whereHas('user', fn($q) => $q->where('category', '1'))->get(),
            'careAvailabilities' => AvailabilityCalendar::whereHas('user', fn($q) => $q->where('category', '2'))->get(),
        ]);
    }


    public function indexByUserId($userId)
    {
        try {
            $shifts = PersonShift::where('user_id', $userId)->get();

            return response()->json([
                'status' => true,
                'data' => $shifts
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching shifts by user_id: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve shifts.'
            ], 500);
        }
    }
    

    public function publishWeek(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        // Example: Update all shifts in that date range as published
        PersonShift::whereBetween('date', [$request->start_date, $request->end_date])
            ->update(['status' => '1']);

        return response()->json(['message' => 'Week published successfully!']);
    }
    
    public function updateOvertime(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'date'             => 'required|date',
            'overtime'         => 'required|in:yes,no',
            'overtime_hours'   => 'nullable|integer|min:0',
            'overtime_minutes' => 'nullable|integer|min:0|max:59',
        ]);

        $shift = Shift::where('user_id', $request->user_id)
                      ->where('date', $request->date)
                      ->first();

        if ($shift) {
            $shift->update([
                'overtime'         => $request->overtime,
                'overtime_hours'   => $request->overtime_hours,
                'overtime_minutes' => $request->overtime_minutes,
            ]);
        } else {
            $shift = Shift::create([
                'user_id'          => $request->user_id,
                'date'             => $request->date,
                'overtime'         => $request->overtime,
                'overtime_hours'   => $request->overtime_hours,
                'overtime_minutes' => $request->overtime_minutes,
            ]);
        }

        return response()->json([
            'status' => true,
            'data'   => $shift
        ]);
    }

    public function cloneUserWeek(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'start_date' => 'required|date',
        ]);

        $userId = $request->user_id;
        $startDate = Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addDays(6);

        $user = User::findOrFail($userId);

        // Fetch all shifts in this week
        $shifts = PersonShift::where('user_id', $userId)->whereBetween('date', [$startDate, $endDate])->get();

        if ($shifts->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No shifts found to clone']);
        }

        foreach ($shifts as $shift) {
            $newDate = Carbon::parse($shift->date)->addWeek();

            // Prepare new data for cloning
            $data = [
                'user_id' => $shift->user_id,
                'date' => $newDate->toDateString(),
                'start_time' => $shift->start_time,
                'end_time' => $shift->end_time,
                'overtime' => $shift->overtime,
                'overtime_hours' => $shift->overtime_hours,
                'overtime_minutes' => $shift->overtime_minutes,
            ];

            // Map shift type & slot
            if ($shift->shift_type == 'Day' || $shift->shift_type == 'LD') {
                $data['shift_slot'] = 'Day';
                $data['shift_type'] = 'LD';
                $data['shift_time'] = $shift->start_time . '-' . $shift->end_time;
            } elseif ($shift->shift_type == 'Night' || $shift->shift_type == 'N1' || $shift->shift_type == 'N2') {
                $data['shift_slot'] = 'Night';
                $data['shift_type'] = $shift->shift_type; // keep N1/N2 if already set
                $data['shift_time'] = $shift->start_time . '-' . $shift->end_time;
            }

            // Role group mapping
            switch ($user->role_id) {
                case 1: $data['role_group'] = 'Admin'; break;
                case 2: $data['role_group'] = 'Normal User'; break;
                case 3: $data['role_group'] = 'T/Leaders'; break;
                case 4: $data['role_group'] = 'Seniors'; break;
                case 5: $data['role_group'] = 'Carers'; break;
                case 6: $data['role_group'] = 'Bank'; break;
                default: $data['role_group'] = 'Unknown'; break;
            }

            $data['person_name'] = $user->name;

            // Update if exists, otherwise create new
            PersonShift::updateOrCreate(
                ['user_id' => $data['user_id'], 'date' => $data['date']],
                $data
            );
        }

        return response()->json(['status' => true, 'message' => 'User shifts cloned successfully']);
    }


    // public function cloneWeek(Request $request)
    // {
    //     $request->validate([
    //         'source_week' => 'required|string', 
    //         'target_week' => 'required|string', 
    //     ]);

    //     [$sourceStart, $sourceEnd] = explode('|', $request->source_week);
    //     [$targetStart, $targetEnd] = explode('|', $request->target_week);

    //     // Fetch all shifts in source week
    //     $shifts = PersonShift::whereBetween('date', [$sourceStart, $sourceEnd])->get();

    //     if ($shifts->isEmpty()) {
    //         return response()->json(['status' => false, 'message' => 'No shifts found to clone']);
    //     }

    //     $roleGroups = [
    //         1 => 'Admin',
    //         2 => 'Normal User',
    //         3 => 'T/Leaders',
    //         4 => 'Seniors',
    //         5 => 'Carers',
    //         6 => 'Bank',
    //         7 => 'Kitchen Manager',
    //         8 => 'Cook/Asst. Cooks',
    //         9 => 'Cleaners',
    //         10 => 'Laundry'
    //     ];

    //     foreach ($shifts as $shift) {
    //         $user = $shift->user;

    //         if (!$user) continue; // skip if user not found

    //         // Calculate day offset from source week start
    //         $dayDiff = Carbon::parse($shift->date)->diffInDays(Carbon::parse($sourceStart));
    //         $newDate = Carbon::parse($targetStart)->addDays($dayDiff)->toDateString();

    //         // Prepare new data
    //         $data = [
    //             'user_id' => $shift->user_id,
    //             'date' => $newDate,
    //             'start_time' => $shift->start_time,
    //             'end_time' => $shift->end_time,
    //             'overtime' => $shift->overtime,
    //             'overtime_hours' => $shift->overtime_hours,
    //             'overtime_minutes' => $shift->overtime_minutes,
    //         ];

    //         // Map shift type & slot
    //         if (in_array($shift->shift_type, ['Day','LD'])) {
    //             $data['shift_slot'] = 'Day';
    //             $data['shift_type'] = 'LD';
    //             $data['shift_time'] = $shift->start_time . '-' . $shift->end_time;
    //         } elseif (in_array($shift->shift_type, ['Night','N1','N2'])) {
    //             $data['shift_slot'] = 'Night';
    //             $data['shift_type'] = $shift->shift_type;
    //             $data['shift_time'] = $shift->start_time . '-' . $shift->end_time;
    //         }

    //         $data['role_group'] = $roleGroups[$user->role_id] ?? 'Unknown';
    //         $data['person_name'] = $user->name;

    //         // Insert or update
    //         PersonShift::updateOrCreate(
    //             ['user_id' => $data['user_id'], 'date' => $data['date']],
    //             $data
    //         );
    //     }

    //     return response()->json(['status' => true, 'message' => 'All user shifts cloned successfully']);
    // }

    public function cloneWeek(Request $request)
    {
        $request->validate([
            'source_week' => 'required|string', 
            'target_week' => 'required|string',
            'type'        => 'required|string|in:all,kitchen,care_day,care_night',
        ]);

        [$sourceStart, $sourceEnd] = explode('|', $request->source_week);
        [$targetStart, $targetEnd] = explode('|', $request->target_week);

        // Fetch all shifts in source week with user relationship
        $shifts = PersonShift::with('user')
            ->whereBetween('date', [$sourceStart, $sourceEnd])
            ->get();

        if ($shifts->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No shifts found to clone']);
        }

        foreach ($shifts as $shift) {
            $user = $shift->user;
            if (!$user) continue;

            // ✅ Apply filters
            if ($request->type === 'kitchen' && $user->category != 1) {
                continue;
            }
            if ($request->type === 'care_day' && !($user->category == 2 && in_array($shift->shift_type, ['Day','LD']))) {
                continue;
            }
            if ($request->type === 'care_night' && !($user->category == 2 && in_array($shift->shift_type, ['Night','N1','N2']))) {
                continue;
            }

            // Calculate new date based on offset
            $dayDiff = Carbon::parse($shift->date)->diffInDays(Carbon::parse($sourceStart));
            $newDate = Carbon::parse($targetStart)->addDays($dayDiff)->toDateString();

            // Prepare new data
            $data = [
                'user_id' => $shift->user_id,
                'date' => $newDate,
                'start_time' => $shift->start_time,
                'end_time' => $shift->end_time,
                'overtime' => $shift->overtime,
                'overtime_hours' => $shift->overtime_hours,
                'overtime_minutes' => $shift->overtime_minutes,
                'person_name' => $user->name,
            ];

            // Map shift slot/type
            if (in_array($shift->shift_type, ['Day','LD'])) {
                $data['shift_slot'] = 'Day';
                $data['shift_type'] = 'LD';
            } elseif (in_array($shift->shift_type, ['Night','N1','N2'])) {
                $data['shift_slot'] = 'Night';
                $data['shift_type'] = $shift->shift_type;
            }

            PersonShift::updateOrCreate(
                ['user_id' => $data['user_id'], 'date' => $data['date']],
                $data
            );
        }

        return response()->json(['status' => true, 'message' => 'Shifts cloned successfully']);
    }

    public function getWeeks(Request $request)
    {
        // Get the year from query param, default to current year
        $year = $request->year ?? now()->year;
    
        $weeks = [];
        $start = Carbon::create($year)->startOfYear();
        $end   = Carbon::create($year)->endOfYear();
    
        $current = $start->copy();
    
        while ($current->lte($end)) {
            // Month title: October 2026
            $month = $current->format('F Y');
    
            if (!isset($weeks[$month])) {
                $weeks[$month] = [];
            }
    
            $weekStart = $current->copy()->startOfWeek();
            $weekEnd   = $current->copy()->endOfWeek();
    
            $weeks[$month][] = [
                'label' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M'),
                'value' => $weekStart->toDateString() . '|' . $weekEnd->toDateString(),
            ];
    
            $current->addWeek();
        }
    
        return response()->json([
            'status' => true,
            'weeks'  => $weeks,
            'year'   => $year,
        ]);
    }


     public function dashbordstore(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'date' => 'required|date',
            'shift_type' => 'required|string',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'overtime' => 'boolean',
            'overtime_hours' => 'nullable|integer',
            'overtime_minutes' => 'nullable|integer',
            'msg' => 'nullable'
        ]);
        if($data['shift_type'] == 'Day' || $data['shift_type'] == 'LD') {
            $data['shift_slot'] = 'Day';
            $data['shift_type'] = 'LD';
            $data['shift_time'] = $data['start_time'] . '-' . $data['end_time'];
        }elseif($data['shift_type'] == 'Night' || $data['shift_type'] == 'N') {
            $data['shift_slot'] = 'Night';
            $data['shift_time'] = $data['start_time'] . '-' . $data['end_time'];
            $data['shift_type'] = 'N1';
        }else{
            $data['shift_slot'] =  "Day";
            $data['shift_time'] = $data['start_time'] . '-' . $data['end_time'];
            $data['shift_type'] = $data['shift_type'];
        }
        $user = User::find($data['user_id']);
         if($user->role_id == 1) {
            $data['role_group'] = 'Admin';
        }elseif($user->role_id == 2) {
            $data['role_group'] = 'Normal User';
        }elseif($user->role_id == 3) {
            $data['role_group'] = 'T/Leaders';
        }elseif($user->role_id == 4) {
            $data['role_group'] = 'Seniors';
        }elseif($user->role_id == 5) {
            $data['role_group'] = 'Carers';
        }elseif($user->role_id == 6) {
            $data['role_group'] = 'Bank';
        }

        $data['person_name'] = $user->name;
        $data['date'] = date('Y-m-d', strtotime($data['date']));
        $shift = PersonShift::updateOrCreate(
            ['user_id' => $data['user_id'], 'date' => $data['date']],
            $data
        );

        return response()->json(['status' => true, 'shift' => $shift]);
    }



//   public function report(Request $request)
//     {
//         // --- 1️⃣ Get date range ---
//         $startDate = $request->start_date 
//             ? Carbon::parse($request->start_date) 
//             : Carbon::now()->startOfWeek();

//         $endDate = $request->end_date 
//             ? Carbon::parse($request->end_date) 
//             : Carbon::now()->endOfWeek();

//         // --- 2️⃣ Load users and their shifts within date range ---
//         $users = User::with(['shifts' => function($q) use ($startDate, $endDate) {
//             $q->whereBetween('date', [$startDate, $endDate]);
//         }])->get();

//         // --- 3️⃣ Initialize report data ---
//         $report = [];
//         $dailyTotals = [];
//         $dailyTotalsCost = [];
//         $grandTotalCost = 0;

//         // --- 4️⃣ Loop through each user ---
//         foreach ($users as $user) {
//             $row = [
//                 'user' => $user->name,
//                 'rate' => $user->rate ?? 0,
//                 'overtime_rate' => $user->overtime_rate ?? 0,
//                 'daily' => [],
//                 'total_minutes' => 0,
//                 'total_overtime' => 0,
//                 'total_cost' => 0,
//             ];

//             // --- 5️⃣ Loop through each shift ---
//             foreach ($user->shifts as $shift) {
//                 // --- Determine total shift minutes ---
//                 if ($shift->start_time && $shift->end_time) {
//                     $start = Carbon::parse($shift->start_time);
//                     $end   = Carbon::parse($shift->end_time);
//                     $minutes = $end->diffInMinutes($start);
//                 } else {
//                     if ($shift->shift_type === 'LD') {
//                         $start = Carbon::parse($shift->date . ' 07:15');
//                         $end   = Carbon::parse($shift->date . ' 20:30');
//                         $minutes = $end->diffInMinutes($start);
//                     } elseif ($shift->shift_type === 'N') {
//                         $start = Carbon::parse($shift->date . ' 20:15');
//                         $end   = Carbon::parse($shift->date)->addDay()->setTime(7, 30);
//                         $minutes = $end->diffInMinutes($start);
//                     } else {
//                         $minutes = 0;
//                     }
//                 }

//                 // --- Handle Overtime (manual + auto) ---
//                 if (!is_null($shift->overtime_hours) || !is_null($shift->overtime_minutes)) {
//                     // Admin-entered overtime
//                     $ot = (($shift->overtime_hours ?? 0) * 60) + ($shift->overtime_minutes ?? 0);
//                 } else {
//                     $ot = 0;
//                 }


//                 // --- Convert to hours ---
//                 $normal_hours = ($minutes - $ot) / 60;
//                 $overtime_hours = $ot / 60;

//                 // --- Apply rate and cost ---
//                 $normal_rate = $user->rate ?? 0;
//                 $overtime_rate = $user->overtime_rate ?? $normal_rate;
//                 $cost = round(($normal_hours * $normal_rate) + ($overtime_hours * $overtime_rate), 2);

//                 // --- Save daily details ---
//                 $row['daily'][$shift->date][] = [
//                     'shift_type' => $shift->shift_type,
//                     'minutes' => $minutes,
//                     'overtime_minutes' => $ot,
//                     'overtime_formatted' => sprintf('%dh %02dm', floor($ot / 60), $ot % 60),
//                     'cost' => $cost,
//                 ];

//                 // --- Update totals per user ---
//                 $row['total_minutes'] += $minutes;
//                 $row['total_overtime'] += $ot;
//                 $row['total_cost'] += $cost;

//                 // --- Update global totals ---
//                 $dailyTotals[$shift->date] = ($dailyTotals[$shift->date] ?? 0) + $minutes;
//                 $dailyTotalsCost[$shift->date] = ($dailyTotalsCost[$shift->date] ?? 0) + $cost;
//                 $grandTotalCost += $cost;
//             }

//             $report[] = $row;
//         }

//         // --- 6️⃣ Return data to view ---
//         return view('reports.shift_report', compact(
//             'report', 
//             'startDate', 
//             'endDate', 
//             'dailyTotals', 
//             'dailyTotalsCost', 
//             'grandTotalCost'
//         ));
//     }

    public function report(Request $request)
    {
        // --- 1️⃣ Get date range ---
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date) 
            : Carbon::now()->startOfWeek();

        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date) 
            : Carbon::now()->endOfWeek();

        // --- 2️⃣ Load users and their shifts within date range ---
        $users = User::with(['shifts' => function($q) use ($startDate, $endDate) {
            $q->whereBetween('date', [$startDate, $endDate]);
        }])->get();

        // --- 3️⃣ Initialize report data ---
        $report = [];
        $dailyTotals = [];
        $dailyTotalsCost = [];
        $grandTotalCost = 0;

        // --- 4️⃣ Loop through each user ---
        foreach ($users as $user) {
            $row = [
                'user' => $user->name,
                'rate' => $user->rate ?? 0,
                'overtime_rate' => $user->overtime_rate ?? 0,
                'daily' => [],
                'total_minutes' => 0,
                'total_overtime' => 0,
                'total_cost' => 0,
            ];

            // --- 5️⃣ Loop through each shift ---
            foreach ($user->shifts as $shift) {
                $start = null;
                $end = null;
                $minutes = 0; 
                 if (!empty($shift->shift_time) && str_contains($shift->shift_time, '-')) {
                    [$startStr, $endStr] = explode('-', $shift->shift_time);

                    $start = Carbon::parse($shift->date . ' ' . trim($startStr));
                    $end = Carbon::parse($shift->date . ' ' . trim($endStr));

                    // Handle overnight shifts (e.g. 8PM–7AM)
                    if ($end->lt($start)) {
                        $end->addDay();
                    }

                    $minutes = $end->diffInMinutes($start);
                } else {
                    if ($shift->shift_type === 'LD') {
                        $start = Carbon::parse($shift->date . ' 07:15');
                        $end   = Carbon::parse($shift->date . ' 20:30');
                        $minutes = $end->diffInMinutes($start);
                    } elseif ($shift->shift_type === 'N') {
                        $start = Carbon::parse($shift->date . ' 20:15');
                        $end   = Carbon::parse($shift->date)->addDay()->setTime(7, 30);
                        $minutes = $end->diffInMinutes($start);
                    } else if($shift->shift_type === 'E') {
                       $start = Carbon::parse($shift->date . ' 7:15');
                        $end   = Carbon::parse($shift->date . '14:00');
                        $minutes = $end->diffInMinutes($start);
                    }else if($shift->shift_type === 'L') {
                        $start = Carbon::parse($shift->date . ' 13:45 ');
                        $end   = Carbon::parse($shift->date . '08:00');
                        $minutes = $end->diffInMinutes($start);
                        
                    }else {
                        $start = Carbon::parse($start);
                        $end   = Carbon::parse($end);
                        $minutes = $end->diffInMinutes($start);
                    }
                }

                // --- Handle Overtime (manual + auto) ---
                if (!is_null($shift->overtime_hours) || !is_null($shift->overtime_minutes)) {
                    // Admin-entered overtime
                    $ot = (($shift->overtime_hours ?? 0) * 60) + ($shift->overtime_minutes ?? 0);
                } else {
                    $ot = 0;
                }

                // --- Convert to hours ---
                $normal_hours = ($minutes) / 60;
                $overtime_hours = $ot / 60;

                // --- Apply rate and cost ---
                $normal_rate = $user->rate ?? 0;
                $overtime_rate = $user->overtime_rate ?? $normal_rate;
                $cost = round(($normal_hours * $normal_rate) + ($overtime_hours * $overtime_rate), 2);
                // dd("normal_hours:-",$normal_hours ,"normal_rate:-", $normal_rate,"cost",$cost,"overtime_hours:-",$overtime_hours ,"overtime_rate:-",$overtime_rate);
                // --- Save daily details ---
                $row['daily'][$shift->date][] = [
                    'shift_type' => $shift->shift_type,
                    'minutes' => $minutes,
                    'overtime_minutes' => $ot,
                    'overtime_formatted' => sprintf('%dh %02dm', floor($ot / 60), $ot % 60),
                    'cost' => $cost,
                ];

                // --- Update totals per user ---
                $row['total_minutes'] += $minutes;
                $row['total_overtime'] += $ot;
                $row['total_cost'] += $cost;

                // --- Update global totals ---
                $dailyTotals[$shift->date] = ($dailyTotals[$shift->date] ?? 0) + $minutes;
                $dailyTotalsCost[$shift->date] = ($dailyTotalsCost[$shift->date] ?? 0) + $cost;
                $grandTotalCost += $cost;
            }

            $report[] = $row;
        }

        // --- 6️⃣ Return data to view ---
        return view('reports.shift_report', compact(
            'report', 
            'startDate', 
            'endDate', 
            'dailyTotals', 
            'dailyTotalsCost', 
            'grandTotalCost'
        ));
    }


    public function exportExcel(Request $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        return Excel::download(new \App\Exports\ShiftReportExport($startDate, $endDate), 'shift_report.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        $users = User::with(['shifts' => function ($q) use ($request) {
            $q->whereBetween('date', [$request->start_date, $request->end_date]);
        }])->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.shift_report_pdf', [
            'users'      => $users,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        return $pdf->download('shift_report.pdf');
    }


    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'shifts' => 'required|array',
                'shifts.*.user_id'     => 'required|integer|exists:users,id',
                'shifts.*.person_name' => 'required|string|max:50',
                'shifts.*.role_group'  => 'nullable|string|max:50',
                'shifts.*.date'        => 'required|date',
                'shifts.*.shift_type'  => 'required|string|max:20',
                'shifts.*.shift_slot'  => 'required|string|in:Day,Night',
                'shifts.*.shift_time'  => 'nullable|string|max:20',
            ]);

            $created = [];

            // Save each shift
            foreach ($validated['shifts'] as $shiftData) {
                $created[] = PersonShift::create($shiftData);
            }

            return response()->json([
                'status' => true,
                'message' => 'Shifts recorded successfully.',
                'data' => $created
            ]);

        } catch (Exception $e) {
            Log::error('Error storing shifts: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to store shifts.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    // public function getWeeklyShifts(Request $request)
    // {
    //     try {
    //         $start = Carbon::parse($request->query('start_date'));
    //         $end   = $start->copy()->addDays(7); // 7-day range

    //         $shifts = PersonShift::with('user')
    //             ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
    //             ->get();

    //         $users = User::all()->groupBy('category');

    //         $gridKitchenHousekeeping = [];
    //         $gridCare = [];

    //         foreach ($users as $category => $group) {
    //             foreach ($group as $user) {
    //                 if (in_array($user->role_id, [0, 1,2])) {
    //                     continue;
    //                 }

    //                 $row = [
    //                     'user_id' => $user->id,
    //                     'name'    => $user->name,
    //                     'role_id' => $user->role_id,
    //                     'slots'   => array_fill(0, 7, null)
    //                 ];

    //                 if ($category == 1) {
    //                     $row['start_time'] = $user->start_time ? date("H:i", strtotime($user->start_time)) : null;
    //                     $row['end_time']   = $user->end_time ? date("H:i", strtotime($user->end_time)) : null;

    //                     $gridKitchenHousekeeping[$user->id] = $row;
    //                 } else {
    //                     $gridCare[$user->id] = $row;
    //                 }
    //             }
    //         }

    //         foreach ($shifts as $shift) {
    //             $dayIndex = Carbon::parse($shift->date)->diffInDays($start);

    //             if ($dayIndex >= 0 && $dayIndex < 7) {
    //                 $slotData = [
    //                     'type'             => $shift->shift_type,
    //                     'slot'             => $shift->shift_slot,
    //                     'time'             => $shift->shift_time,
    //                     'overtime'         => $shift->overtime,
    //                     'overtime_hours'   => $shift->overtime_hours,
    //                     'overtime_minutes' => $shift->overtime_minutes
    //                 ];

    //                 if (isset($gridKitchenHousekeeping[$shift->user_id])) {
    //                     $gridKitchenHousekeeping[$shift->user_id]['slots'][$dayIndex] = $slotData;
    //                 }

    //                 if (isset($gridCare[$shift->user_id])) {
    //                     $gridCare[$shift->user_id]['slots'][$dayIndex] = $slotData;
    //                 }
    //             }
    //         }

    //         // ✅ define role maps here, so frontend doesn’t hardcode
    //         $kitchenRoles = [
    //             ['id' => 7, 'label' => 'Kitchen Manager'],
    //             ['id' => 8, 'label' => 'Cooks'],
    //             ['id' => 9, 'label' => 'Cook/Asst.'],
    //             ['id' => 10, 'label' => 'Cleaners'],
    //             ['id' => 11, 'label' => 'Laundry'],
    //             ['id' => 6, 'label' => 'Bank'],
    //         ];

    //         $careDayRoles = [
    //             ['id' => 3, 'label' => 'T/Leaders'],
    //             ['id' => 4, 'label' => 'Seniors'],
    //             ['id' => 5, 'label' => 'Carers'],
    //             ['id' => 6, 'label' => 'Bank'],
    //         ];

    //         $careNightRoles = [
    //             ['id' => 4, 'label' => 'Seniors'],
    //             ['id' => 5, 'label' => 'Carers'],
    //             ['id' => 6, 'label' => 'Bank'],
    //         ];

    //         return response()->json([
    //             'status'  => true,
    //             'start_date' => $start->toDateString(),
    //             'kitchen_housekeeping_grid' => array_values($gridKitchenHousekeeping),
    //             'care_grid' => array_values($gridCare),
    //             'role_maps' => [
    //                 'kitchen'   => $kitchenRoles,
    //                 'care_day'  => $careDayRoles,
    //                 'care_night'=> $careNightRoles
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error($e->getMessage());
    //         return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }

    public function getWeeklyShifts(Request $request)
    {
        try {
            $start = Carbon::parse($request->query('start_date'));
            $end   = $start->copy()->addDays(6); // 7-day range (Mon-Sun)

            // ✅ fetch shifts
            $shifts = PersonShift::with('user')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get();

            // ✅ fetch leave requests
            $leaveRequests = \App\Models\LeaveRequest::where('status', 1)
                ->where(function($q) use ($start, $end) {
                    $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function($q2) use ($start, $end) {
                        $q2->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
                })
                ->get();

            // ✅ expand leaves into [user_id][date] = true
            $leaves = [];
            foreach ($leaveRequests as $leave) {
                $leaveStart = Carbon::parse($leave->start_date);
                $leaveEnd   = Carbon::parse($leave->end_date);

                for ($d = $leaveStart->copy(); $d->lte($leaveEnd); $d->addDay()) {
                    if ($d->between($start, $end)) {
                        $leaves[$leave->user_id][$d->toDateString()] = true;
                    }
                }
            }

            // ✅ users grouped by category
            $users = User::all()->groupBy('category');

            $gridKitchenHousekeeping = [];
            $gridCare = [];

            foreach ($users as $category => $group) {
                foreach ($group as $user) {
                    if (in_array($user->role_id, [0, 1, 2])) continue;

                    $row = [
                        'user_id' => $user->id,
                        'name'    => $user->name,
                        'role_id' => $user->role_id,
                        'slots'   => array_fill(0, 7, null),
                    ];

                    if ($category == 1) {
                        $row['start_time'] = $user->start_time ? date("H:i", strtotime($user->start_time)) : null;
                        $row['end_time']   = $user->end_time ? date("H:i", strtotime($user->end_time)) : null;
                        $gridKitchenHousekeeping[$user->id] = $row;
                    } else {
                        $gridCare[$user->id] = $row;
                    }
                }
            }

            // ✅ apply shifts
            foreach ($shifts as $shift) {
                $dayIndex = Carbon::parse($shift->date)->diffInDays($start);

                if ($dayIndex >= 0 && $dayIndex < 7) {
                    $isLeave = isset($leaves[$shift->user_id][$shift->date]);

                    $slotData = [
                        'type'             => $shift->shift_type,
                        'slot'             => $shift->shift_slot,
                        'time'             => $shift->shift_time,
                        'overtime'         => $shift->overtime,
                        'overtime_hours'   => $shift->overtime_hours,
                        'overtime_minutes' => $shift->overtime_minutes,
                        'leave_request'    => $isLeave,
                        'status'           => $shift->status,
                        'msg'              => $shift->msg
                    ];

                    if (isset($gridKitchenHousekeeping[$shift->user_id])) {
                        $gridKitchenHousekeeping[$shift->user_id]['slots'][$dayIndex] = $slotData;
                    }
                    if (isset($gridCare[$shift->user_id])) {
                        $gridCare[$shift->user_id]['slots'][$dayIndex] = $slotData;
                    }
                }
            }

            // ✅ also mark leave days (even without shift)
            foreach ($leaves as $userId => $days) {
                foreach ($days as $date => $_) {
                    $dayIndex = Carbon::parse($date)->diffInDays($start);
                    if ($dayIndex >= 0 && $dayIndex < 7) {
                        $slotData = [
                            'type'             => null,
                            'slot'             => null,
                            'time'             => null,
                            'overtime'         => null,
                            'overtime_hours'   => null,
                            'overtime_minutes' => null,
                            'leave_request'    => true,
                        ];

                        if (isset($gridKitchenHousekeeping[$userId]) && !$gridKitchenHousekeeping[$userId]['slots'][$dayIndex]) {
                            $gridKitchenHousekeeping[$userId]['slots'][$dayIndex] = $slotData;
                        }
                        if (isset($gridCare[$userId]) && !$gridCare[$userId]['slots'][$dayIndex]) {
                            $gridCare[$userId]['slots'][$dayIndex] = $slotData;
                        }
                    }
                }
            }

            // ✅ define roles
            $kitchenRoles = [
                ['id' => 7, 'label' => 'Kitchen Manager'],
                ['id' => 8, 'label' => 'Cooks'],
                ['id' => 9, 'label' => 'Cook/Asst.'],
                ['id' => 10, 'label' => 'Cleaners'],
                ['id' => 11, 'label' => 'Laundry'],
                ['id' => 6, 'label' => 'Bank'],
            ];

            $careDayRoles = [
                ['id' => 3, 'label' => 'T/Leaders'],
                ['id' => 4, 'label' => 'Seniors'],
                ['id' => 5, 'label' => 'Carers'],
                ['id' => 6, 'label' => 'Bank'],
            ];

            $careNightRoles = [
                ['id' => 4, 'label' => 'Seniors'],
                ['id' => 5, 'label' => 'Carers'],
                ['id' => 6, 'label' => 'Bank'],
            ];

            return response()->json([
                'status'  => true,
                'start_date' => $start->toDateString(),
                'kitchen_housekeeping_grid' => array_values($gridKitchenHousekeeping),
                'care_grid' => array_values($gridCare),
                'role_maps' => [
                    'kitchen'   => $kitchenRoles,
                    'care_day'  => $careDayRoles,
                    'care_night'=> $careNightRoles,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteShift(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $request->date = date('Y-m-d', strtotime($request->date));
        
        $deleted = PersonShift::where('user_id', $request->user_id)->where('date', $request->date)->delete();

        if($deleted){
            return response()->json(['status' => true]);
        }

        return response()->json(['status' => false, 'message' => 'Shift not found or could not delete']);
    }




    public function index()
    {
        $definitions = ShiftDefinition::all();

        $dayShift = $definitions->firstWhere('shift_slot', 'Day');
        $nightShift = $definitions->firstWhere('shift_slot', 'Night');
         $n2 = $definitions->firstWhere('shift_slot', 'N2');

        $leaveDefinitions = $definitions->whereNotNull('leave_code')->values()->map(function ($item) {
            return [
                'code' => $item->leave_code,
                'name' => $item->leave_name,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'day_shift' => $dayShift ? [
                    'label' => 'LD',
                    'time' => $dayShift->day_start . ' - ' . $dayShift->day_end,
                    'break' => $dayShift->break_duration,
                    'note' => $dayShift->total_working_time,
                ] : null,

                'night_shift' => $nightShift ? [
                    'label' => 'N1',
                    'time' => $nightShift->night_start . ' - ' . $nightShift->night_end,
                    'break' => $nightShift->break_duration,
                    'note' => $nightShift->total_working_time,
                ] : null,
                'N2' => $n2 ? [
                    'label' => 'N2',
                    'time' => $n2->night_start . ' - ' . $n2->night_end,
                    'break' => $n2->break_duration,
                    'note' => $n2->total_working_time,
                ] : null,

                'leaves' => $leaveDefinitions
            ]
        ]);
    }
    
    
//     public function getAvailabilityByUserId($user_id)
// {
//     try {
//         $shifts = PersonShift::where('user_id', $user_id)
//             ->orderBy('date', 'asc')
//             ->get();

//         $user=User::find($user_id);
//         $grouped = [];

//         foreach ($shifts as $shift) {
//             $date = $shift->date;

//             if (!isset($grouped[$date])) {
//                 $grouped[$date] = [
//                     'Date'         => $date,
//                     'Day shift'    => '',
//                     'In'           => 0,
//                     'Night shift'  => '',
//                     'N'            => 0,
//                 ];
//             }

//             if ($shift->shift_slot === 'Day') {
//                 $grouped[$date]['Day shift'] = $shift->shift_type;
//                 $grouped[$date]['In'] = $shift->shift_type === 'LD' ? 1 : 0;
//             }

//             if ($shift->shift_slot === 'Night') {
//                 $grouped[$date]['Night shift'] = $shift->shift_type;
//                 $grouped[$date]['N'] = $shift->shift_type === 'N' ? 1 : 0;
//             }
//         }

//         return response()->json([
//             'Available list' => array_values($grouped)
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Error fetching availability',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }

// public function getAvailabilityByUserId($user_id)
// {
//     try {
//         $shifts = PersonShift::where('user_id', $user_id)
//             ->orderBy('date', 'asc')
//             ->get();

//         $user = User::find($user_id);
//         $grouped = [];

//         foreach ($shifts as $shift) {
//             $date = $shift->date;

//             if (!isset($grouped[$date])) {
//                 $grouped[$date] = [
//                     'Date'         => $date,
//                     'Day shift'    => '',
//                     'In'           => 0,
//                     'Night shift'  => '',
//                     'N'            => 0,
//                 ];
//             }

//             if ($user && $user->category == 1) {
//                 // ✅ Ignore shift_slot completely
//                 $grouped[$date]['Day shift'] = 
//                     date("H:i", strtotime($user->start_time)) . '-' . date("H:i", strtotime($user->end_time));
//                 $grouped[$date]['In'] = 1; 
//                 $grouped[$date]['shift_type'] = $shift->shift_type;
//                 $grouped[$date]['shift_time'] = $shift->shift_time;
//             } else {
//                 // Normal Day shift logic
//                 if ($shift->shift_slot === 'Day') {
//                     $grouped[$date]['Day shift'] = $shift->shift_type;
//                     $grouped[$date]['In'] = $shift->shift_type === 'LD' ? 1 : 0;
//                     $grouped[$date]['shift_type'] = $shift->shift_type;
//                      $grouped[$date]['shift_time'] = $shift->shift_time;
//                 }

//                 // Normal Night shift logic
//                 if ($shift->shift_slot === 'Night') {
//                     $grouped[$date]['Night shift'] = $shift->shift_type;
//                     $grouped[$date]['N'] = $shift->shift_type === 'N' ? 1 : 0;
//                     $grouped[$date]['shift_type'] = $shift->shift_type;
//                     $grouped[$date]['shift_time'] = $shift->shift_time;
//                 }
                
//             }
//         }

//         return response()->json([
//             'Available list' => array_values($grouped)
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Error fetching availability',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }

// public function getAvailabilityByUserId($user_id)
// {
//     try {
//         $shifts = PersonShift::where('user_id', $user_id)
//             ->orderBy('date', 'asc')
//             ->get();

//         $user = User::find($user_id);
//         $grouped = [];

//         foreach ($shifts as $shift) {
//             $date = $shift->date;

//             if (!isset($grouped[$date])) {
//                 $grouped[$date] = [
//                     'Date'        => $date,
//                     'Day shift'   => '',
//                     'In'          => 0,
//                     'Night shift' => '',
//                     'N'           => 0,
//                     'shift_type'  => '',
//                     'shift_time'  => '',
//                     'login'       => $user ? $user->login : null, // ✅ add login here
//                 ];
//             }

//             if ($user && $user->category == 1) {
//                 // ✅ Ignore shift_slot completely
//                 $grouped[$date]['Day shift'] = 
//                     date("H:i", strtotime($user->start_time)) . '-' . date("H:i", strtotime($user->end_time));
//                 $grouped[$date]['In'] = 1; 
//                 $grouped[$date]['shift_type'] = $shift->shift_type;
//                 $grouped[$date]['shift_time'] = $shift->shift_time;
//             } else {
//                 // Normal Day shift logic
//                 if ($shift->shift_slot === 'Day') {
//                     $grouped[$date]['Day shift'] = $shift->shift_type;
//                     $grouped[$date]['In'] = $shift->shift_type === 'LD' ? 1 : 0;
//                     $grouped[$date]['shift_type'] = $shift->shift_type;
//                     $grouped[$date]['shift_time'] = $shift->shift_time;
//                 }

//                 // Normal Night shift logic
//                 if ($shift->shift_slot === 'Night') {
//                     $grouped[$date]['Night shift'] = $shift->shift_type;
//                     $grouped[$date]['N'] = $shift->shift_type === 'N' ? 1 : 0;
//                     $grouped[$date]['shift_type'] = $shift->shift_type;
//                     $grouped[$date]['shift_time'] = $shift->shift_time;
//                 }
//             }
//         }

//         return response()->json([
//             'Available list' => array_values($grouped)
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Error fetching availability',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }


public function getAvailabilityByUserId($user_id)
{
    try {
        $shifts = PersonShift::where('user_id', $user_id)
            ->orderBy('date', 'asc')
            ->where('status','1')
            ->get();

        $user = User::find($user_id);
        $grouped = [];

        foreach ($shifts as $shift) {
            $date = $shift->date;

            if (!isset($grouped[$date])) {
                $grouped[$date] = [
                    'Date'        => $date,
                    'Day shift'   => '',
                    'In'          => 0,
                    'Night shift' => '',
                    'N'           => 0,
                    'shift_type'  => '',
                    'shift_time'  => '',
                    'login'       => $user ? $user->name : null, // ✅ FIXED
                ];
            }

            if ($user && $user->category == 1) {
                // ✅ Handle both Day & Night separately
                if ($shift->shift_slot === 'Day') {
                    $grouped[$date]['Day shift'] = date("H:i", strtotime($user->start_time)) . '-' . date("H:i", strtotime($user->end_time));
                    $grouped[$date]['In'] = 1;
                } elseif ($shift->shift_slot === 'Night') {
                    $grouped[$date]['Night shift'] = date("H:i", strtotime($user->start_time)) . '-' . date("H:i", strtotime($user->end_time));
                    $grouped[$date]['N'] = 1;
                }
                $grouped[$date]['shift_type'] = $shift->shift_type . " " . $shift->msg;
                $grouped[$date]['shift_time'] = $shift->shift_time;
                $grouped[$date]['shift_slot'] = $shift->shift_slot;
                $grouped[$date]['overtime'] = $shift->overtime;
                $grouped[$date]['overtime_hours'] = $shift->overtime_hours;
                $grouped[$date]['overtime_minutes'] = $shift->overtime_minutes;
            } else {
                // ✅ Normal Day shift logic
                if ($shift->shift_slot === 'Day') {
                    $grouped[$date]['Day shift'] = $shift->shift_type;
                    $grouped[$date]['In'] = $shift->shift_type === 'LD' ? 1 : 0;
                    $grouped[$date]['shift_type'] = $shift->shift_type . " " .$shift->msg;
                    $grouped[$date]['shift_time'] = $shift->shift_time;
                    $grouped[$date]['overtime'] = $shift->overtime;
                    $grouped[$date]['overtime_hours'] = $shift->overtime_hours;
                    $grouped[$date]['overtime_minutes'] = $shift->overtime_minutes;
                    $grouped[$date]['shift_slot'] = $shift->shift_slot;
                     
                }

                // ✅ Normal Night shift logic
                if ($shift->shift_slot === 'Night') {
                    $grouped[$date]['Night shift'] = $shift->shift_type . " " . $shift->msg;
                    $grouped[$date]['N'] = $shift->shift_type === 'N' ? 1 : 0;
                    $grouped[$date]['shift_type'] = $shift->shift_type . " " . $shift->msg ;
                    $grouped[$date]['shift_time'] = $shift->shift_time;
                    $grouped[$date]['overtime'] = $shift->overtime;
                    $grouped[$date]['overtime_hours'] = $shift->overtime_hours;
                    $grouped[$date]['overtime_minutes'] = $shift->overtime_minutes;
                    $grouped[$date]['shift_slot'] = $shift->shift_slot;
                }
            }
        }

        return response()->json([
            'Available list' => array_values($grouped)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching availability',
            'error' => $e->getMessage()
        ], 500);
    }
}






}
