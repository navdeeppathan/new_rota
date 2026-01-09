<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\Notification;

class AdminNotificationController extends Controller
{
    /**
     * Return unread notifications for admin
     */
    public function getAdminNotifications(Request $request)
    {
        try {
            // If you want to use authenticated admin, use auth()->id() or guard
            $adminId = $request->id ?? $request->user()->id ?? null;

            // Fetch unread notifications intended for admin (read_at = null)
            $notifications = Notification::with('alertType', 'user')
                ->where('show_to_admin', true)
                ->whereNull('read_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'count' => $notifications->count(),
                'notifications' => $notifications
            ], 200);

        } catch (\Exception $e) {
            Log::error('getAdminNotifications error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admin notifications.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:notifications,id'
            ]);

            $notification = Notification::findOrFail($request->id);
            $notification->read_at = Carbon::now();
            $notification->save();

            return response()->json(['success' => true, 'message' => 'Notification marked as read'], 200);

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
