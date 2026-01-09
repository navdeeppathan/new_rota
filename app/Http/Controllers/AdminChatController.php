<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\Attachment;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class AdminChatController extends Controller
{
    public function getConversations(Request $request)
    {
        return view('admin.chat.index');
    }

    public function getUsersList()
    {
        $users = User::where('role_id', '!=', 1)->get();

        return response()->json($users);
    }

    public function fetchMessages(Request $request)
    {
        $request->validate([
            'user_1' => 'required|exists:users,id',
            'user_2' => 'required|exists:users,id'
        ]);

        $messages = Message::with('attachments', 'sender')
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

        return view('admin.chat.messages', compact('messages'));
    }

    public function sendMessage(Request $request)
    {
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
            'show_to_admin' => 0,
            'show_to_superadmin' => 0,
            'show_to_all' => 0,
        ];
        
        switch ($receiver->role_id) {
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
    }
}
