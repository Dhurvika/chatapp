<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth-user-id" content="{{ Auth::id() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Chat App</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">

    <x-app-layout>
        <!-- Outer Wrapper to center everything horizontally -->
        <div class="flex justify-center py-10">

            <!-- Inner Container (White box, all chat content) -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6 w-4/5"
                style="height: 600px; display: flex; flex-direction: column;">

                <!-- Title or Info (optional) -->
                <h3 class="text-lg font-semibold mb-4">Users</h3>

                <!-- Main Chat Area: user list (left) + chat window (right) -->
                <div class="flex-1 flex overflow-hidden">

                    <!-- Left: User List -->
                    <div id="user-list" class="w-1/3 border-r border-gray-300 pr-4 mr-4 overflow-y-auto">
                        @if ($users->isEmpty())
                        <p>No users available to chat.</p>
                        @else
                        @foreach ($users as $user)
                        <div class="user-item cursor-pointer p-2 border-b flex justify-between items-center"
                            data-id="{{ $user->id }}"
                            onclick="loadMessages('{{ $user->id }}')">
                            <span>{{ $user->name }}</span>
                            <span id="unread-{{ $user->id }}" class="badge hidden"
                                style="background: green; color: white; padding: 5px 8px; border-radius: 50%; font-size: 12px;">
                            </span>
                        </div>
                        @endforeach
                        @endif
                    </div>


                    <!-- Right: Chat Window (hidden by default) -->
                    <div id="chat-window"
                        class="w-2/3 flex flex-col"
                        style="display: none;">

                        <!-- Message Container (scrollable) -->
                        <div id="message-container"
                            class="border p-4 flex-grow overflow-y-auto bg-gray-50 rounded">
                            <!-- Chat messages appear here -->
                        </div>
                    </div>
                </div> <!-- end .flex-1.flex.overflow-hidden -->

                <!-- Bottom: Input & Send Button -->
                <div class="mt-4 flex">
                    <input type="text"
                        id="message-input"
                        class="border p-2 flex-grow"
                        placeholder="Type a message...">
                    <button id="send-message"
                        class="ml-2 px-4 py-2 bg-blue-500 text-white rounded">
                        Send
                    </button>
                </div>

            </div> <!-- end .bg-white -->
        </div> <!-- end .flex.justify-center -->
    </x-app-layout>

</body>

</html>