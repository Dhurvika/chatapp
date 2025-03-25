<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-900">
                    {{ __("You're logged in!") }}
                </div>

                <!-- Go to Chat Button -->
                <div class="mt-6 flex justify-center">
                    <a href="{{ route('chat') }}"
                        style="display: inline-block; padding: 10px 20px; background-color: blue; color: white; text-decoration: none; border-radius: 5px;">
                        Go to Chat
                    </a>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>