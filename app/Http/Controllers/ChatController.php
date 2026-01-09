<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\Attachment;
use App\Models\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'sender_id' => 'required|exists:users,id',
                'receiver_id' => 'required|exists:users,id',
                'message' => 'nullable|string',
                'file' => 'nullable|file|max:10240'
            ]);

            $filePath = null;
            $fileName = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('chat_documents'), $fileName);
                $filePath = 'chat_documents/' . $fileName;
            }

            $message = Message::create([
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'is_read' => 0,
            ]);

            if ($filePath) {
                Attachment::create([
                    'sender_id' => $request->sender_id,
                    'receiver_id' => $request->receiver_id,
                    'message_id' => $message->id,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                ]);
            }

            $sender = User::find($request->sender_id);
            $receiver = User::find($request->receiver_id);
            
            $senderName = $sender->name ?: 'Someone';
            
            $notificationData = [
                'alert_type_id' => 2,
                'message' => "{$senderName} sent you a message.",
                'show_to_user' => 0,
                'show_to_admin' => 1,
                'show_to_superadmin' => 0,
                'show_to_all' => 0,
            ];
            
            switch ($receiver->role) {
                case '1':
                    $notificationData['show_to_admin'] = 1;
                    $notificationData['admin_id'] = $receiver->id;
                    break;
            
                case '1':
                    $notificationData['show_to_superadmin'] = 1;
                    $notificationData['superadmin_id'] = $receiver->id;
                    break;
            
                default:
                    $notificationData['show_to_user'] = 1;
                    $notificationData['user_id'] = $receiver->id;
                    break;
            }
            
            Notification::create($notificationData);


            return response()->json(['success' => true, 'data' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function fetchMessages(Request $request)
    {
        try {
            $request->validate([
                'user_1' => 'required|exists:users,id',
                'user_2' => 'required|exists:users,id'
            ]);

            $messages = Message::with('attachments')
                ->where(function ($q) use ($request) {
                    $q->where('sender_id', $request->user_1)
                      ->where('receiver_id', $request->user_2);
                })
                ->orWhere(function ($q) use ($request) {
                    $q->where('sender_id', $request->user_2)
                      ->where('receiver_id', $request->user_1);
                })
                ->orderBy('created_at')
                ->get();

            return response()->json(['success' => true, 'messages' => $messages]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateMessage(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:messages,id',
                'message' => 'required|string'
            ]);

            $message = Message::findOrFail($request->id);
            $message->update(['message' => $request->message]);

            return response()->json(['success' => true, 'data' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteMessage(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:messages,id'
            ]);

            $message = Message::findOrFail($request->id);

            $attachments = $message->attachments;

            foreach ($attachments as $file) {
                if (File::exists(public_path($file->file_path))) {
                    File::delete(public_path($file->file_path));
                }
                $file->delete();
            }

            $message->delete();

            return response()->json(['success' => true, 'message' => 'Message and attachments deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'sender_id' => 'required|exists:users,id'
            ]);

            Message::where('receiver_id', $request->receiver_id)
                ->where('sender_id', $request->sender_id)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);

            return response()->json(['success' => true, 'message' => 'Messages marked as read']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getConversations(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $userId = $request->user_id;

            $latestMessageIds = Message::selectRaw('MAX(id) as id')
                ->where('sender_id', $userId)
                ->orWhere('receiver_id', $userId)
                ->groupByRaw('LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)')
                ->pluck('id');

            $lastMessages = Message::with(['sender', 'receiver'])
                ->whereIn('id', $latestMessageIds)
                ->orderByDesc('created_at')
                ->get();

            $data = $lastMessages->map(function ($message) use ($userId) {
                $otherUser = $message->sender_id == $userId ? $message->receiver : $message->sender;

                $unreadCount = Message::where('sender_id', $otherUser->id)
                    ->where('receiver_id', $userId)
                    ->where('is_read', 0)
                    ->count();

                return [
                    'user' => [
                        'id' => $otherUser->id,
                        'name' => trim($otherUser->firstname . ' ' . $otherUser->lastname) ?: ($otherUser->name ?? $otherUser->email),
                        'is_online' => $otherUser->is_online,
                        'last_seen' => $otherUser->last_seen
                    ],
                    'last_message' => $message->message ?? 'File',
                    'last_time' => $message->created_at,
                    'unread_count' => $unreadCount
                ];
            });

            return response()->json(['success' => true, 'conversations' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateOnlineStatus(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'is_online' => 'required|boolean'
            ]);

            $user = User::findOrFail($request->user_id);
            $user->update([
                'is_online' => $request->is_online,
                'last_seen' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User online status updated',
                'data' => [
                    'user_id' => $user->id,
                    'is_online' => $user->is_online,
                    'last_seen' => $user->last_seen
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
