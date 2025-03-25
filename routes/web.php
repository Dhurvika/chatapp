<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;


// 🔹 Redirect `/` to `/login`
Route::get('/', function () {
    return redirect('/login');
});

// 🔹 Dashboard (only authenticated users can access)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 🔹 Authentication routes (Login, Register)
require __DIR__ . '/auth.php';

// 🔹 Chat routes (only authenticated users can access)
Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/messages', [MessageController::class, 'sendMessage']);
    Route::get('/messages/{receiverId}', [MessageController::class, 'getMessages']);
    Route::delete('/messages/clear/{receiverId}', [MessageController::class, 'clearChat']);
    Route::post('/messages/mark-as-read/{receiverId}', [MessageController::class, 'markMessagesAsRead']);
    Route::get('/messages/unread-count', [MessageController::class, 'getUnreadMessagesCount']);

    // ✅ Route: Get Users with Unread Message Counts
    Route::get('/get-users-with-unread-count', function () {
        $users = \App\Models\User::where('id', '!=', Auth::id())
            ->get()
            ->map(function ($user) {
                $unreadCount = Message::where('sender_id', $user->id)
                    ->where('receiver_id', Auth::id())
                    ->where('is_read', false)
                    ->count();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'unread_count' => $unreadCount
                ];
            });

        return response()->json($users);
    });


    // ✅ Route to mark messages as read
    Route::post('/mark-messages-as-read/{receiverId}', function ($receiverId) {
        Message::where('sender_id', $receiverId)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    });
});


// 🔹 Guest routes (only for users who are not logged in)
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});
