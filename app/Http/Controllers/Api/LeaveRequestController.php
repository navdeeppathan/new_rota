<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;
use App\Models\PersonShift;

class LeaveRequestController extends Controller
{
    
    
    public function index()
    {
        try {
            $leaves = LeaveRequest::with('user')->orderBy('created_at', 'desc')->get();
            return view('admin.leave_requests.index', compact('leaves')); // Adjust the view path
        } catch (\Exception $e) {
            Log::error('Leave index error: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch leave requests.');
        }
    }


    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'task_id'        => 'nullable|integer',
                'user_id'        => 'nullable|integer',
                'title'          => 'nullable|string|max:100',
                'description'    => 'nullable|string',
                'duration_days'  => 'nullable|integer',
                'start_date'     => 'required|date',
                'end_date'       => 'required|date|after_or_equal:start_date',
                'leave_type'     => 'nullable|string|max:100',
                
            ]);

            $leave = LeaveRequest::create($data);
            // Send notifications
            $this->sendNotifications($leave);

            return response()->json(['success' => true, 'message' => 'Leave request created', 'data' => $leave]);
        } catch (\Exception $e) {
            Log::error('Leave store error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create leave request'], 500);
        }
    }
    
    private function sendNotifications($leave)
    {
        $user  = User::find($leave->user_id);
        $admin = User::where('role_id', 1)->first();
    
        $userName = $user->name;
    
        // Format dates
        $fromDate = date('j M', strtotime($leave->start_date)); // e.g., 2 Feb
        $toDate   = date('j M', strtotime($leave->end_date));   // e.g., 5 Feb
        $nextDutyDate = date('jS \\of M', strtotime($leave->end_date . ' +1 day')); // e.g., 6th of Feb
    
        // Define messages
        $adminMessage = "New leave request submitted by {$userName}. Please review it.";
        $userMessageSubmitted  = "Your leave request has been submitted successfully. Awaiting admin approval.";
        $userMessageApproved = "Your sick leave has been approved\n{$fromDate} - {$toDate}\nYou will be on duty {$nextDutyDate}";
    
        // Send notification to admin
        if ($admin) {
            Notification::create([
                'alert_type_id' => 2, // user_related
                'message'       => $adminMessage,
                'show_to_admin' => 1,
                'user_id'       => $user->id,
                'admin_id'      => $admin->id,
            ]);
        }
    
        // Send submitted notification to user
        if ($user) {
            Notification::create([
                'alert_type_id' => 2,
                'message'       => $userMessageSubmitted,
                'show_to_user'  => 1,
                'user_id'       => $user->id,
                'admin_id'      => $admin->id,
            ]);
        }
    
        // Send approved notification to user (you can move this to your approval method)
        if ($leave->status === 'approved') {
            Notification::create([
                'alert_type_id' => 2,
                'message'       => $userMessageApproved,
                'show_to_user'  => 1,
                'user_id'       => $user->id,
                'admin_id'      => $admin->id,
            ]);
        }
    }


    // public function updateReason(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'leave_ids' => 'required|array|min:1',
    //             'leave_ids.*' => 'integer|exists:leave_requests,id',
    //             'reason' => 'nullable|string',
    //             'status' => 'required|integer'
    //         ]);
    
    //         LeaveRequest::whereIn('id', $validated['leave_ids'])
    //             ->update([
    //                 'reason' => $validated['reason'],
    //                 'status' => $validated['status']
    //             ]);
    //         $user  = User::find($leave->user_id);
    //         $admin = User::where('role_id', 1)->first();
    //         if($validated['status'] == 1) {
    //             Notification::create([
    //                 'alert_type_id' => 2,
    //                 'message'       => "Your leave request has been approved.",
    //                 'show_to_user'  => 1,
    //                 'user_id'       => $user->id,
    //                 'admin_id'      => $admin->id,
    //             ]);
    //         } else {
    //             Notification::create([
    //                 'alert_type_id' => 2,
    //                 'message'       => "Your leave request has been rejected.",
    //                 'show_to_user'  => 1,
    //                 'user_id'       => $user->id,
    //                 'admin_id'      => $admin->id,
    //             ]);
    //         }
                        
    
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Leave requests updated successfully.'
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Leave reason/status update error: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to update leave requests.'
    //         ], 500);
    //     }
    // }
    
    public function updateReason(Request $request)
    {
        try {
            $validated = $request->validate([
                'leave_ids'   => 'required|array|min:1',
                'leave_ids.*' => 'integer|exists:leave_requests,id',
                'reason'      => 'nullable|string',
                'status'      => 'required|integer'
            ]);
    
            // Fetch all leave requests
            $leaveRequests = LeaveRequest::whereIn('id', $validated['leave_ids'])->get();
            
            $admin = User::where('role_id', 1)->first();
    
            foreach ($leaveRequests as $leave) {
                
                $leave->update([
                    'reason' => $validated['reason'],
                    'status' => $validated['status']
                ]);
                $user = User::find($leave->user_id);
                $message = $validated['status'] == 1
                    ? "Your leave request has been approved."
                    : "Your leave request has been rejected \nReason: " . $validated['reason'];
    
                Notification::create([
                    'alert_type_id' => 2,
                    'message'       => $message,
                    'show_to_user'  => 1,
                    'user_id'       => $user->id,
                    'admin_id'      => $admin->id ?? null,
                ]);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Leave requests updated successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Leave reason/status update error: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave requests.'
            ], 500);
        }
    }







    public function show($id)
    {
        try {
            $leave = LeaveRequest::with(['user', 'task'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => $leave]);
        } catch (\Exception $e) {
            Log::error('Leave show error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Leave request not found'], 404);
        }
    }
    


    public function updateStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'leave_ids' => 'required|array|min:1',
                'leave_ids.*' => 'exists:leave_requests,id',
                'status' => 'required|integer|in:0,1,2,3',
            ]);
    
            $leaveRequests = LeaveRequest::whereIn('id', $validated['leave_ids'])->get();
    
            LeaveRequest::whereIn('id', $validated['leave_ids'])->update([
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);
    
            // Status text mapping
            $statusText = match((int)$validated['status']) {
                0 => 'Pending',
                1 => 'Approved',
                2 => 'Rejected',
                3 => 'Cancelled',
                default => 'Updated',
            };
    
            // Leave type to short code mapping
            $leaveTypeMap = [
                'Sick' => 'S',
                'Annual Leave' => 'A/L',
                'Compassionate Leave' => 'CL',
                'Unpaid Leave' => 'UL',
                'Self isolation' => 'SI',
            ];
    
            $admin = User::where('role_id', 1)->first();
    
            foreach ($leaveRequests as $leave) {
                $user = User::find($leave->user_id);
    
                if ($user && $admin) {
                    $startDate = Carbon::parse($leave->start_date);
                    $endDate = Carbon::parse($leave->end_date);
    
                    $startFormatted = $startDate->format('j M');
                    $endFormatted = $endDate->format('j M');
                    $dateRange = "{$startFormatted} - {$endFormatted}";
    
                    $totalDays = $startDate->diffInDays($endDate) + 1;
                    $onDutyDate = $endDate->copy()->addDay()->format('jS \o\f M');
                    $statusTextFormatted = ucfirst(strtolower($statusText));
    
                    // Create notification
                    $title = "Your {$leave->title} has been {$statusTextFormatted}";
                    $message = "{$dateRange}     {$totalDays} d\nYou will be on duty {$onDutyDate}";
    
                    Notification::create([
                        'alert_type_id' => 2, // user_related
                        'message'       => "{$title}\n{$message} \nReason: {$leave->reason}",
                        'show_to_user'  => 1,
                        'user_id'       => $user->id,
                        'admin_id'      => $admin->id,
                    ]);
    
                    // ✅ Update person_shifts if status is Approved
                    if ((int)$validated['status'] === 1) {
                        $shortCode = $leaveTypeMap[$leave->leave_type] ?? 'LV'; // default to 'LV' if unknown
    
                        $currentDate = $startDate->copy();
                        while ($currentDate->lte($endDate)) {
                            PersonShift::where('user_id', $leave->user_id)
                                ->whereDate('date', $currentDate->format('Y-m-d'))
                                ->update(['shift_type' => $shortCode]);
    
                            $currentDate->addDay();
                        }
                    }
                }
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Leave request status updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Leave status update error: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave request status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    
    public function getLeaveRequestsByUserIdGrouped(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
    
            $userId = $request->input('user_id');
            $today = \Carbon\Carbon::today()->toDateString();
    
            // Today's requests (where today falls between start_date and end_date)
            $todayLeaves = LeaveRequest::where('user_id', $userId)
                // ->whereDate('start_date', '<=', $today)
                // ->whereDate('end_date', '>=', $today)
                ->orderBy('start_date', 'desc')
                ->get();
    
            // History: before today
            $historyLeaves = LeaveRequest::where('user_id', $userId)
                // ->whereDate('end_date', '<', $today)
                // ->orderBy('start_date', 'desc')
                ->get();
    
     \Log::info($historyLeaves);             \Log::info($todayLeaves);
            return response()->json([
                'success' => true,
                'message' => 'Leave requests grouped by today and history.',
                'data' => [
                    'today'   => $todayLeaves,
                    'history' => $historyLeaves,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Leave fetch error: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leave requests.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function update(Request $request, $id)
    {
        try {
            $leave = LeaveRequest::findOrFail($id);

            $data = $request->validate([
                'task_id'        => 'nullable|integer',
                'user_id'        => 'nullable|integer',
                'title'          => 'nullable|string|max:100',
                'description'    => 'nullable|string',
                'duration_days'  => 'nullable|integer',
                'start_date'     => 'required|date',
                'end_date'       => 'required|date|after_or_equal:start_date',
                'leave_type'     => 'nullable|string|max:100',
            ]);

            $leave->update($data);

            return response()->json(['success' => true, 'message' => 'Leave request updated', 'data' => $leave]);
        } catch (\Exception $e) {
            Log::error('Leave update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update leave request'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $leave = LeaveRequest::findOrFail($id);
            $leave->delete();
            return response()->json(['success' => true, 'message' => 'Leave request deleted']);
        } catch (\Exception $e) {
            Log::error('Leave delete error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete leave request'], 500);
        }
    }
    
    public function getLeavesByUserId($userId)
{
    try {
        // Define the leave type to code map
        $leaveCodeMap = [
            'Sick' => 'S',
            'Annual Leave' => 'A/L',
            'Compassionate Leave' => 'CL',
            'Unpaid Leave' => 'UL',
            'Self isolation' => 'SI',
        ];

        // Fetch all leaves for the given user
        $leaves = LeaveRequest::where('user_id', $userId)->get(['start_date', 'leave_type']);

        // Transform the result with codes
        $formatted = $leaves->map(function ($leave) use ($leaveCodeMap) {
            return [
                'start_date' => $leave->start_date,
                'leave_code' => $leaveCodeMap[$leave->leave_type] ?? null
            ];
        });

        return response()->json(['success' => true, 'data' => $formatted]);

    } catch (\Exception $e) {
        Log::error('Get Leaves error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to fetch leave data'], 500);
    }
}

}
