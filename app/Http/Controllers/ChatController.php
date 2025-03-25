<?php

namespace App\Http\Controllers;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index()
    {
        // ✅ Ensure user is authenticated
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'You must be logged in to access the chat.');
        }

        // ✅ Fetch all users except the logged-in user
        $users = User::where('id', '!=', Auth::id())->get();

        // ✅ Log fetched users for debugging
        Log::info("Users fetched for chat:", $users->toArray());

        return view('chat.index', compact('users'));
    }

    public function getUsersWithUnreadCount()
    {
        $userId = Auth::id();

        $users = User::where('id', '!=', $userId)->get();

        foreach ($users as $user) {
            $user->unread_count = Message::where('sender_id', $user->id)
                ->where('receiver_id', $userId)
                ->where('is_read', false) // Assuming you have an `is_read` column
                ->count();
        }

        return response()->json($users);
    }
}
