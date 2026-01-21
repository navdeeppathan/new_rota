<?php

use App\Http\Controllers\Admin\ComplianceController;
use App\Http\Controllers\Admin\CqcVaultController;
    use App\Http\Controllers\Admin\TaskManagementController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\PageController;
    use App\Http\Controllers\ScheduleController;
    use App\Http\Controllers\TaskScheduleController;
    use App\Http\Controllers\PrivacyPolicyController;
    use App\Http\Controllers\Api\LeaveRequestController;
    use App\Http\Controllers\Api\AvailabilityCalendarController;
    use App\Models\User;
    use App\Http\Controllers\PersonShiftController;
    use App\Http\Controllers\BroadcastController;
    use App\Http\Controllers\ShiftDefinitionController;
    use App\Http\Controllers\AdminChatController;
    use App\Http\Controllers\Api\AuthController;
    use App\Http\Controllers\ChatController;
    use App\Http\Controllers\TaskController;
    use App\Models\PrivacyPolicy;
    use App\Http\Controllers\Auth\CustomPasswordController;
    use Dompdf\FrameDecorator\Page;

    Route::get('forgot-password', [CustomPasswordController::class, 'showForgotForm'])->name('forgot.password');
    Route::post('forgot-password', [CustomPasswordController::class, 'sendResetLink'])->name('forgot.password.send');

    Route::get('change-password/{email}', [CustomPasswordController::class, 'showChangePasswordForm'])->name('change.password');
    Route::post('change-password', [CustomPasswordController::class, 'updatePassword'])->name('change.password.update');


    Route::get('/privacy-policy', function () {
        $policy = PrivacyPolicy::select('content')->latest()->first();
    
        return view('welcome', compact('policy'));
    });
    
     
    Route::get('/contact_us', function () {
        return view('contactus');
    });
    
    
    Route::prefix('admin/chat')->middleware(['isLoggedIn'])->group(function () {
            Route::get('/', [AdminChatController::class, 'getConversations'])->name('admin.chat');
            Route::get('/users', [AdminChatController::class, 'getUsersList'])->name('admin.chat.users');
            Route::get('/messages', [AdminChatController::class, 'fetchMessages'])->name('admin.chat.messages');
            Route::post('/send', [AdminChatController::class, 'sendMessage'])->name('admin.chat.send');
        });
    // Admin route to create, update, and view Privacy Policy in one editor
    Route::match(['get', 'post'], '/admin/privacy-policy', [PrivacyPolicyController::class, 'manage'])
        ->middleware(['isLoggedIn']) // remove 'is_admin' if not using admin role middleware
        ->name('admin.privacy.manage');
    
      Route::get('/admin/notifications', [App\Http\Controllers\AdminNotificationController::class, 'getAdminNotifications'])->name('admin.notifications.list');
    Route::post('/admin/notifications/mark-read', [App\Http\Controllers\AdminNotificationController::class, 'markAsRead'])->name('admin.notifications.markRead');
    Route::get('admin/notifications/all', [App\Http\Controllers\AdminNotificationController::class, 'getAdminNotifications'])->name('admin.notifications.all');

    
    Route::get('/shift-definitions/create', [ShiftDefinitionController::class, 'create'])->name('shift-definitions.create');
    Route::post('/shift-definitions', [ShiftDefinitionController::class, 'store'])->name('shift-definitions.store');
    
    Route::get('/user/shift-definitions', [ShiftDefinitionController::class, 'getByUserId']);
    
    
    Route::prefix('admin')->middleware(['isLoggedIn'])->group(function () {
        Route::resource('broadcasts', BroadcastController::class);
    });

    Route::prefix('leave-requests')->middleware('isLoggedIn')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
        Route::post('/leave/update-status', [LeaveRequestController::class, 'updateStatus'])->name('leave-requests.updateStatus');
    });
    
     Route::get('/reports/shifts', [PersonShiftController::class, 'report'])->name('reports.shifts');
    
    Route::middleware('isLoggedIn')->group(function () {
       Route::get('/shifts/create', [PersonShiftController::class, 'create'])->name('shifts.create');
        Route::post('/shifts/store', [PersonShiftController::class, 'store'])->name('shifts.store');
        Route::post('/save-shift', [PersonShiftController::class, 'store']);  
    });
    
    Route::get('/reports/shifts', [PersonShiftController::class, 'report'])->name('reports.shifts');
    Route::get('/reports/shifts/export-excel', [PersonShiftController::class, 'exportExcel'])->name('reports.shifts.excel');
    Route::get('/reports/shifts/export-pdf', [PersonShiftController::class, 'exportPdf'])->name('reports.shifts.pdf');

    
    Route::get('/', [PageController::class, 'login'])->name('login');
    
    Route::get('/login', [PageController::class, 'login'])->name('login');
    Route::post('/login/check', [PageController::class, 'loginCheck'])->name('login.check');
    Route::post('/logout', [PageController::class, 'logout'])->name('logout');
    
    Route::get('/superadmin/login', [PageController::class, 'superadminlogin'])->name('superadmin.login');
    Route::post('/superadminlogin/check', [PageController::class, 'superadminloginCheck'])->name('superadmin.login.check');
    
   
    Route::middleware('isLoggedIn')->group(function () {
        Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [PageController::class, 'users'])->name('users');
        Route::get('/users/create', [PageController::class, 'createUser'])->name('users.create');
        Route::post('/users', [PageController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{id}/edit', [PageController::class, 'editUser'])->name('users.edit');
        Route::post('/users/{id}', [PageController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [PageController::class, 'deleteUser'])->name('users.delete');
    });
    
    
    
    Route::prefix('task-schedule')->middleware('isLoggedIn')->group(function () {
        Route::get('/', [TaskScheduleController::class, 'index'])->name('task_perform.index');
        Route::get('/create', [TaskScheduleController::class, 'create'])->name('task_perform.create');
        Route::post('/', [TaskScheduleController::class, 'store'])->name('task_perform.store');
        Route::get('/{id}/edit', [TaskScheduleController::class, 'edit'])->name('task_perform.edit');
        Route::put('/{id}', [TaskScheduleController::class, 'update'])->name('task_perform.update');
        Route::delete('/{id}', [TaskScheduleController::class, 'destroy'])->name('task_perform.destroy');
        Route::get('/assign', [TaskScheduleController::class, 'assignView'])->name('task_perform.assign');
        Route::post('/assign', [TaskScheduleController::class, 'assignSchedulesToTask'])->name('task_perform.assign_multiple');
        Route::get('/tasks_user/by-user/{user_id}', [TaskScheduleController::class, 'getByUser']);
    });



    Route::middleware(['isLoggedIn'])->prefix('schedule')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('schedule.index');
        Route::get('/create', [ScheduleController::class, 'create'])->name('schedule.create');
        Route::post('/', [ScheduleController::class, 'store'])->name('schedule.store');
        Route::get('/{id}/edit', [ScheduleController::class, 'edit'])->name('schedule.edit');
        Route::post('/{id}', [ScheduleController::class, 'update'])->name('schedule.update');
        Route::delete('/{id}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');
    });


    Route::middleware(['isLoggedIn'])->prefix('availability')->group(function () {
            Route::get('/create', function () {
                $users = User::where('role_id', '!=', 1)
                             ->select('id', 'name')
                             ->get();
            
                return view('availability.create', compact('users'));
            })->name('availability.create');
    
        Route::get('/index', [AvailabilityCalendarController::class, 'index'])->name('availability.index');
    
        // Store availability data
        Route::post('/schedule/store', [AvailabilityCalendarController::class, 'store'])->name('availability.store');
    
    });



Route::get('cqc-index/', [CqcVaultController::class,'index']);
Route::get('cqc-vault', [CqcVaultController::class,'index']);

Route::get('admin/dashboard', [CqcVaultController::class,'dashboard']);
Route::get('admin/checklist-frequency', [CqcVaultController::class,'checklistfrequency']);
Route::get('admin/checklist-cqc', [CqcVaultController::class,'checklist']);

    Route::get('admin/compliance', [ComplianceController::class, 'index']);

    Route::get('admin/compliance/checklist', [ComplianceController::class, 'getChecklist']);

    Route::post('admin/compliance/update-check', [ComplianceController::class, 'updateCheck']);

// Audit logs
Route::get('cqc-vault/audit-logs', [CqcVaultController::class,'auditLogs']);

// Folder creation
Route::get('cqc-vault/folder/create', [CqcVaultController::class,'createFolderPage']);
Route::post('cqc-vault/folder/create', [CqcVaultController::class,'createFolder']);

// Folder view (show documents & subfolders)
Route::get('cqc-vault/folder/{id}', [CqcVaultController::class,'viewFolder']);

// Upload document
Route::post('cqc-vault/upload', [CqcVaultController::class,'upload']);

// Document history
Route::get('cqc-vault/history/{id}', [CqcVaultController::class,'history']);

// Add multiple subfolders
Route::post('cqc-vault/folder/{id}/subfolders', [CqcVaultController::class,'addSubfolders']);

// Delete a folder
Route::delete('cqc-vault/folder/{id}', [CqcVaultController::class,'dFolder']);
Route::delete('cqc-vault/folder/{id}', [CqcVaultController::class,'deleteFolder']);
Route::delete('cqc-vault/document/{id}', [CqcVaultController::class, 'deleteDocument']);


Route::get('/tasks/create', [TaskManagementController::class, 'create'])->name('tasks.create');
Route::get('cqc-vault/all-tasks', [TaskManagementController::class, 'index2'])->name('tasks.index2');

Route::get('cqc-vault/tasks', [TaskManagementController::class, 'index'])->name('tasks.index');
Route::post('/tasks', [TaskManagementController::class, 'store'])->name('tasks.store');

Route::get('cqc-vault/tasks/{id}/edit', [TaskManagementController::class, 'edit'])->name('tasks.edit');
Route::post('cqc-vault/tasks/{id}/update', [TaskManagementController::class, 'update'])->name('tasks.update');

Route::get('/tasks/delete/{id}', [TaskManagementController::class, 'destroy'])->name('tasks.delete');

Route::post('/cqc-vault/tasks/{id}/progress',
    [TaskManagementController::class,'updateProgress']);


Route::middleware(['isLoggedIn'])->prefix('superadmin')->name('superadmin.')->group(function () {

    /* ===================== Dashboard ===================== */
    Route::get('/', [PageController::class, 'index'])->name('dashboard');


    Route::get('/users', [PageController::class, 'index'])->name('users.index');
    Route::get('/users/create', [PageController::class, 'storeSuperAdminCreate'])->name('users.create');
    Route::post('/users/store', [PageController::class, 'storeSuperAdminUser'])->name('users.store');
});
