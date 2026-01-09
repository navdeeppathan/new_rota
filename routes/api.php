<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\TaskPerformController;

use App\Http\Controllers\Api\ShiftTypeController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\AvailabilityCalendarController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\PersonShiftController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\ChatController;

Route::prefix('chat')->group(function () {
    Route::post('/send', [ChatController::class, 'sendMessage']);
    Route::post('/fetch', [ChatController::class, 'fetchMessages']);
    Route::post('/update', [ChatController::class, 'updateMessage']);
    Route::post('/delete', [ChatController::class, 'deleteMessage']);
    Route::post('/read', [ChatController::class, 'markAsRead']);
    Route::post('/conversations', [ChatController::class, 'getConversations']);
    Route::post('/status', [ChatController::class, 'updateOnlineStatus']);
});


Route::get('/broadcasts', [BroadcastController::class, 'getAllBroadcasts']);
Route::post('/notifications/create', [AuthController::class, 'createDummyForAdmin']);
Route::post('availability/schedule/store', [AvailabilityCalendarController::class, 'store'])->name('availability.store');


// Session-based login/register routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/logout',   [AuthController::class, 'logout']);
Route::get('/me',        [AuthController::class, 'me']);
Route::post('/profile-pic', [AuthController::class, 'storeOrUpdateProfilePic']);
Route::get('/get-user', [AuthController::class, 'getUser']);
Route::post('/update-user', [AuthController::class, 'updateUser']);
Route::post('/reset-password', [AuthController::class, 'reset']);
Route::post('/user/status', [AuthController::class, 'updateStatus']);
Route::get('/users-by-role/{user_id}', [AuthController::class, 'getUsersByRole']);
Route::post('/update-toggle', [AuthController::class, 'toggle']);
Route::post('/store-users', [AuthController::class, 'storeUsers']);
Route::get('/updated-status', [AuthController::class, 'getStatus']);

Route::get('/weekly-shifts', [PersonShiftController::class, 'getWeeklyShifts']);

Route::post('/clone-user-week', [PersonShiftController::class, 'cloneUserWeek']);


Route::get('/shift-legend', [PersonShiftController::class, 'index']);

Route::post('/delete-shift', [PersonShiftController::class, 'deleteShift']);

Route::get('/user-role-users', [AuthController::class, 'getUserShifts']);

Route::get('/shifts-user/{user_id}', [PersonShiftController::class, 'indexByUserId']);
Route::post('/shifts-user', [PersonShiftController::class, 'store']);
Route::get('/availability-user/{user_id}', [PersonShiftController::class, 'getAvailabilityByUserId']);

Route::post('/save-shift', [PersonShiftController::class, 'dashbordstore']);
Route::patch('/shifts/{id}', [PersonShiftController::class, 'updateOvertime']);
Route::get('/shifts', [PersonShiftController::class, 'index']);
Route::post('/shifts/overtime', [PersonShiftController::class, 'updateOvertime']);

Route::post('/clone-user-week', [PersonShiftController::class, 'cloneUserWeek']);
Route::patch('/shifts/{shift}', [PersonShiftController::class, 'update']);

Route::post('/clone-week', [PersonShiftController::class,'cloneWeek']);
Route::get('weeks', [PersonShiftController::class, 'getWeeks']);


Route::post('availability/store', [AvailabilityCalendarController::class, 'store']);
Route::get('availability/user', [AvailabilityCalendarController::class, 'getUserAvailability']);

Route::post('/publish-week', [PersonShiftController::class, 'publishWeek']);
Route::prefix('notifications')->group(function () {
    Route::get('/user/{id}', [NotificationController::class, 'getUserNotifications']);
    Route::get('/admin/{id}', [NotificationController::class, 'getAdminNotifications']);
    Route::get('/superadmin/{id}', [NotificationController::class, 'getSuperadminNotifications']);
    Route::post('/mark-as-read', [NotificationController::class, 'markAsRead']);
});



Route::prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index']);           // GET all tasks
    Route::post('/', [TaskController::class, 'store']);          // POST new task
    Route::get('/{id}', [TaskController::class, 'show']);        // GET one task
    Route::put('/{id}', [TaskController::class, 'update']);      // PUT update task
    Route::delete('/{id}', [TaskController::class, 'destroy']);
    Route::get('/by-user/get', [TaskController::class, 'getUserTasksWithPerformance']);
    Route::get('/by-date/get', [TaskController::class, 'getTasksByUserDate']);

// DELETE task
});





Route::prefix('leave-requests')->group(function () {
    // Route::get('/', [LeaveRequestController::class, 'index'])->name('leave.index');        // List all
    Route::post('/', [LeaveRequestController::class, 'store']);       // Create
    Route::get('{id}', [LeaveRequestController::class, 'show']);      // Show one
    Route::put('{id}', [LeaveRequestController::class, 'update']);    // Update
    Route::delete('{id}', [LeaveRequestController::class, 'destroy']);
    // Route::post('/leave/update-status', [LeaveRequestController::class, 'updateStatus'])->name('leave-requests.updateStatus');
    Route::get('/userleaves/get', [LeaveRequestController::class, 'getLeaveRequestsByUserIdGrouped']);
    Route::get('/user/{userId}', [LeaveRequestController::class, 'getLeavesByUserId']);
    Route::post('/update-reason', [LeaveRequestController::class, 'updateReason'])->name('leave-requests.updateReason');

// Delete
});

Route::prefix('shifts')->group(function () {
    Route::get('/', [ShiftController::class, 'index']);
    Route::post('/', [ShiftController::class, 'store']);
    Route::put('/update', [ShiftController::class, 'update']);
    Route::delete('delete', [ShiftController::class, 'destroy']);
});

Route::prefix('shift-types')->group(function () {
    Route::get('/', [ShiftTypeController::class, 'index']);
    Route::post('/', [ShiftTypeController::class, 'store']);
    Route::put('/update', [ShiftTypeController::class, 'update']);
    Route::delete('/delete', [ShiftTypeController::class, 'destroy']);
});




// Store a single task performance
Route::post('/task-perform/store', [TaskPerformController::class, 'store']);
Route::get('/tasks/today/get', [TaskPerformController::class, 'getTodayTaskPerformances']);
// Route::get('/task-performances/by-date', [TaskPerformController::class, 'getTaskPerformancesByDate']);
Route::get('/task-performances/by-date', [TaskPerformController::class, 'getScheduleTasksByDate']);
// Route::get('/schedule-tasks/by-date', [TaskPerformController::class, '']);

// Update multiple task-user entries with shared start/end time
Route::put('/task-perform/shared-update', [TaskPerformController::class, 'updateMultipleWithSharedTime']);

