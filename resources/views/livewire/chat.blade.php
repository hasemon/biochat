<?php

use Livewire\Volt\Component;

new class extends Component {
    public $users;
    public $selectedUser;
    public $newMessage;
    public $messages;

    public function mount()
    {
        $this->users = \App\Models\User::whereNot('id', \Illuminate\Support\Facades\Auth::id())->latest()->get();
        $this->selectedUser = $this->users->first();
        $this->loadMessages();
    }

    public function selectUser($id)
    {
        $this->selectedUser = \App\Models\User::find($id);
        $this->loadMessages();
    }

    public function submit()
    {
        if (!$this->newMessage) return;

        $message = \App\Models\ChatMessage::query()->create([
            'sender_id' => \Illuminate\Support\Facades\Auth::id(),
            'receiver_id' => $this->selectedUser->id,
            'message' => $this->newMessage
        ]);

        $this->messages->push($message);

        $this->newMessage = '';
    }

    public function loadMessages()
    {
        $this->messages = \App\Models\ChatMessage::query()
            ->where(fn($q) => $q->where('sender_id', \Illuminate\Support\Facades\Auth::id())->where('receiver_id',
                $this->selectedUser->id))
            ->orWhere(fn($q) => $q->where('sender_id', $this->selectedUser->id)->where('receiver_id',
                \Illuminate\Support\Facades\Auth::id()))
            ->latest()->get();
    }

}; ?>

<div>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Chat') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Start Instance Messaging') }}</flux:subheading>
        <flux:separator variant="subtle"/>
    </div>

    <div class="flex h-[550px] text-sm border rounded-xl shadow overflow-hidden bg-white">

        <div class="w-1/4 border-r bg-gray-50">
            <div class="p-4 font-bold text-gray-700 border-b">Users</div>
            <div class="divide-y">
                @foreach($users as $user)
                    <div wire:click="selectUser({{ $user->id }})"
                         class="p-3 cursor-pointer hover:bg-blue-100 transition {{ $selectedUser->id === $user->id ? 'bg-blue-100 font-bold': '' }}">
                        <div class="text-gray-800">{{ $user->name }}</div>
                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Chat Section -->
        <div class="w-3/4 flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b bg-gray-50">
                <div class="text-lg font-semibold text-gray-800">{{ $selectedUser->name }}</div>
                <div class="text-xs text-gray-500">{{ $selectedUser->email }}</div>
            </div>

            <!-- Messages -->
            <div class="flex-1 p-4 overflow-y-auto space-y-2 bg-gray-50">
                @foreach($messages as $message)
                    <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs px-4 py-2 rounded-2xl shadow text-white {{ $message->sender_id === auth()->id() ? 'bg-blue-600' : 'bg-green-600' }}">
                            {{ $message->message }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Input -->
            <form wire:submit="submit" class="p-4 border-t bg-white flex items-center gap-2">
                <input
                        wire:model="newMessage"
                        type="text"
                        class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-300"
                        placeholder="Type your message..."/>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-full
                transition">
                    Send
                </button>
            </form>
        </div>
    </div>

</div>
