<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // ✅ Import Log



class MessageController extends Controller
{
     // ✅ Fetch messages between logged-in user & selected receiver
     public function getMessages($receiverId)
     {
         $userId = Auth::id();
     
         $messages = Message::where(function ($query) use ($userId, $receiverId) {
             $query->where('sender_id', $userId)->where('receiver_id', $receiverId);
         })->orWhere(function ($query) use ($userId, $receiverId) {
             $query->where('sender_id', $receiverId)->where('receiver_id', $userId);
         })
         ->orderBy('created_at', 'asc')
         ->get();
     
         // Convert timestamps from UTC to local timezone
         $messages->transform(function ($message) {
             return [
                 'id' => $message->id,
                 'sender_id' => $message->sender_id,
                 'receiver_id' => $message->receiver_id,
                 'message' => $message->message,
                 'created_at' => $message->created_at->timezone('Asia/Kolkata')->format('Y-m-d H:i:s'),
             ];
         });
     
         return response()->json($messages);
     }
     
     
     // ✅ Send message & return it instantly
     public function sendMessage(Request $request)
     {
         $request->validate([
             'receiver_id' => 'required|exists:users,id',
             'message' => 'required|string',
         ]);
 
         $message = Message::create([
             'sender_id' => Auth::id(),
             'receiver_id' => $request->receiver_id,
             'message' => $request->message,
             'is_read' => false,
         ]);
 
         return response()->json(['success' => true, 'message' => $message]);
     }
 
     // ✅ Get unread messages count for notification badge
     public function getUnreadMessagesCount()
     {
         // Returns something like: { "2": 1, "5": 3, "7": 0, ... }
         // where key = sender_id, value = unread_count
         $unread = Message::where('receiver_id',  Auth::id())
             ->where('is_read', false)
             ->groupBy('sender_id')
             ->selectRaw('sender_id, COUNT(*) as unread_count')
             ->pluck('unread_count', 'sender_id');
     
         return response()->json($unread);
     }
     
     // ✅ Mark messages as read when opening chat
     public function markMessagesAsRead($receiverId)
     {
         Message::where('receiver_id', Auth::id())
             ->where('sender_id', $receiverId)
             ->where('is_read', false)
             ->update(['is_read' => true]);
 
         return response()->json(['success' => true]);
     }
}
