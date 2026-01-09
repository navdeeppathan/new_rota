<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    
    // public function getUserNotifications($id)
    // {
    //     try {
    //         $notifications = Notification::with('alertType')
    //             ->where('user_id', $id)
    //             ->where('show_to_user', true)
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         return response()->json([
    //             'success' => true,
    //             'count' => $notifications->count(),
    //             'notifications' => $notifications
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('getUserNotifications error: ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch user notifications.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    
    public function getUserNotifications($id)
    {
        try {
            $notifications = Notification::with('alertType')
                ->where('user_id', $id)
                ->where('show_to_user', true)
                ->orderBy('created_at', 'desc')
                ->get();
    
            $notifications->transform(function ($n) {
                if (isset($n->role_id))            $n->role_id = (string) $n->role_id;
                if (isset($n->user_id))            $n->user_id = (string) $n->user_id;
                if (isset($n->show_to_user))       $n->show_to_user = (string) $n->show_to_user;
                if (isset($n->show_to_all))        $n->show_to_all = (string) $n->show_to_all;
                if (isset($n->show_to_superadmin)) $n->show_to_superadmin = (string) $n->show_to_superadmin;
                if (isset($n->show_to_admin))      $n->show_to_admin = (string) $n->show_to_admin;
                if (isset($n->status))             $n->status = (string) $n->status;
                if (isset($n->alert_type_id))      $n->alert_type_id = (string) $n->alert_type_id;
    
                return $n;
            });
    
            return response()->json([
                'success' => true,
                'count' => $notifications->count(),
                'notifications' => $notifications
            ]);
    
        } catch (\Exception $e) {
            Log::error('getUserNotifications error: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user notifications.',
            ], 500);
        }
}



    public function getSuperadminNotifications($id)
    {
        try {
            $notifications = Notification::with('alertType')
                ->where('superadmin_id', $id)
                ->where('show_to_superadmin', true)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'count' => $notifications->count(),
                'notifications' => $notifications
            ]);
        } catch (\Exception $e) {
            Log::error('getSuperadminNotifications error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch superadmin notifications.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAdminNotifications($id)
    {
        try {
            $notifications = Notification::with('alertType')
                ->where('admin_id', $id)
                
                ->where('show_to_admin', true)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'count' => $notifications->count(),
                'notifications' => $notifications
            ]);
        } catch (\Exception $e) {
            Log::error('getAdminNotifications error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admin notifications.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:notifications,id'
            ]);

            $notification = Notification::find($request->id);
            $notification->read_at = Carbon::now();
            $notification->save();
            
            return response()->json(['success' => true, 'message' => 'Notification marked as read']);
        } catch (\Exception $e) {
            Log::error('markAsRead error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
